<?php

namespace Vapnik;


use Bitrix\Main\UserTable;

class Main
{
	public static function CheckGoodsForOrders($arBasket) //Метод очищающий список ненужных элементов корзины
	{
		$result = FALSE; //инициализируем выходные данные
		if ($arBasket)
		{
			$arBoughtItems = [];
			foreach ($arBasket as $arItem) // Получаем список ненужных элементов
			{
				if ($arItem['ORDER_ID'] > 0)
				{
					$arBoughtItems[] = $arItem['PRODUCT_ID'];
				}
			}
			foreach ($arBasket as $key => $arItem) // Очищаем ненужные элементы
			{
				if (in_array($arItem['PRODUCT_ID'], $arBoughtItems)
					|| ($arItem['ORDER_ID'] > 0)
					|| ($arItem['DELAY'] != 'Y')//
				)
				{
					unset($arBasket[$key]);
				}
			}
			$result = $arBasket; //Заполняем выходную переменную
		}

		return $result;

	}

	public static function SendNotifications() // Основной метод
	{
		\CModule::IncludeModule('sale');
		$arBasketItems = [];
		// Получаем список всех элементов корзины за последний месяц.
		// Нам нужны только те элементы, которые добавили в корзину авторизованные пользователи
		$rawBasket = \CSaleBasket::GetList([], [
			'>DATE_UPDATE' => date('d.m.Y h:i:s', mktime(date("H"), date("i"), date("s"), date("n"), date('d') - 30)),
			'!USER_ID'     => FALSE
		]);

		while ($arBasket = $rawBasket->Fetch())
		{
			// Запоминаем пользователей, у которых есть товары в корзине
			$arBasketItems[$arBasket['USER_ID']][] = $arBasket;

		}
		unset($rawBasket);
		$arNewBasketItems = [];
		$arUserIDs = [];
		foreach ($arBasketItems as $key => $arGroup)
		{
			$arNewBasketItems[$key] = self::CheckGoodsForOrders($arGroup);// Очищаем от "лишних"
			if ($arNewBasketItems[$key])
			{
				$arUserIDs[] = $key; // Добавляем в массив ID пользователя, у которого что-либо еще осталось
			} else
			{
				unset($arNewBasketItems[$key]); // Удаляем пустые массивы
			}

		}
		unset($arBasketItems);
		// Получаем список пользователей
		$rawUsers = UserTable::getList([
			'filter' => [
				'ID' => $arUserIDs
			]
		]);
		$arUsers = $rawUsers->fetchAll();


//		d($arUsers);
		foreach ($arNewBasketItems as $user => $arBasketGroup)//Формируем и отправляем письма
		{
			$GoodsList = '<table><tr><td>Название</td><td>Стоимость</td></tr>';
			foreach ($arBasketGroup as $arItem)
			{
				$GoodsList .= '<tr><td>' . $arItem['NAME'] . '</td><td>'
					. CurrencyFormat($arItem['PRICE'], $arItem['CURRENCY']) . '</td></tr>';
			}
			$GoodsList .= '</table>';
			$arFields = [
				'FIRST_NAME' => $arUsers[$user]['NAME'],
				'LAST_NAME'  => $arUsers[$user]['LAST_NAME'],
				'EMAIL_TO'   => $arUsers[$user]['EMAIL'],
				'ITEMS'      => $GoodsList
			];
			lo($arFields);
			\CEvent::Send('VAPNIK_SUBSCRIBE', 's1', $arFields);

		}
	}


}