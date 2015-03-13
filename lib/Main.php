<?php

namespace Vapnik;


use Bitrix\Main\UserTable;

class Main
{
	public static function CheckGoodsForOrders($arBasket)
	{
		$result = FALSE;
		if ($arBasket)
		{
//			d($arBasket);
			$arBoughtItems = [];
			foreach ($arBasket as $arItem)
			{
				if ($arItem['ORDER_ID'] > 0)
				{
					$arBoughtItems[] = $arItem['PRODUCT_ID'];
				}
			}
//			d($arBoughtItems);
			foreach ($arBasket as $key => $arItem)
			{
				if (in_array($arItem['PRODUCT_ID'], $arBoughtItems)
					|| ($arItem['ORDER_ID'] > 0)
					|| ($arItem['DELAY'] != 'Y')
				)
				{
					unset($arBasket[$key]);
				}
			}
			$result = $arBasket;
		}

		return $result;

	}

	public static function SendNotifications()
	{
		\CModule::IncludeModule('sale');
		$arBasketItems = [];
		$rawBasket = \CSaleBasket::GetList([], [
			'>DATE_UPDATE' => date('d.m.Y h:i:s', mktime(date("H"), date("i"), date("s"), date("n") - 1)),
			'!USER_ID'     => FALSE
		]);
		while ($arBasket = $rawBasket->Fetch())
		{
			$arBasketItems[$arBasket['USER_ID']][] = $arBasket;
		}
		$arNewBasketItems = [];
		$arUserIDs = [];
		foreach ($arBasketItems as $key => $arGroup)
		{
			$arNewBasketItems[$key] = self::CheckGoodsForOrders($arGroup);
			if ($arNewBasketItems[$key])
			{
				$arUserIDs[] = $key;
			} else
			{
				unset($arNewBasketItems[$key]);
			}

		}
		$rawUsers = UserTable::getList([
			'filter' => [
				'ID' => $arUserIDs
			]
		]);
		$arUsers = $rawUsers->fetchAll();
		unset($arBasketItems);
//		d($arUsers);
		foreach ($arNewBasketItems as $user => $arBasketGroup)
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
			d($arFields);
			lo($arFields);
			\CEvent::Send('VAPNIK_SUBSCRIBE', 's1', $arFields);

		}
	}


}