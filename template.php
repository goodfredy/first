<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
CModule::IncludeModule('iblock');

	//формирование регистра сведений всех вариаций печей
	$rs_kod = CIBlockElement::GetList(
		array("SORT"=>"ASC"),
		array(
			"IBLOCK_ID" => 558,
			array()
		),
		false,
		false,
		array("ID","NAME","PROPERTY_UF_KOD")
	);
	$rs_array = [];
	$tmp_name_arr = [];
	while($ar = $rs_kod->GetNext()) {
		array_push($tmp_name_arr[$ar["NAME"]], array());
	}

	$tmp_name_arr = array_keys($tmp_name_arr);
	for ($i = 0; $i < count($tmp_name_arr); $i++) {
		$tmp_name = '';
		$tmp_arr = [];
		$rs_kod_inner = CIBlockElement::GetList(
			array("SORT"=>"ASC"),
			array(
				"IBLOCK_ID" => 55,
				array()
			),
			false,
			false,
			array("ID","NAME","PROPERTY_UF_KOD")
		);
		while($ar = $rs_kod_inner->GetNext()) {
			if ($tmp_name_arr[$i] == $ar["NAME"]){
				$tmp_name = $ar["NAME"];
				array_push($tmp_arr, $ar["PROPERTY_UF_KOD_VALUE"]);
			}
		}
		array_push($rs_array, array($tmp_name,$tmp_arr));
	}

	$varmodel_array = array();
	$varmodeldiam_array = array();
	foreach ($rs_array as $pizza_model) {
		$col_p = substr($pizza_model[0], 0, 1);
		$diam_p = substr($pizza_model[0], 2, 2);
		$col_ur = substr($pizza_model[0], 5, 1);
		$varmodel_array[intval($col_p)][intval($diam_p)] = intval($col_ur);
	}
	foreach ($rs_array as $pizza_model) {
		$col_p = substr($pizza_model[0], 0, 1);
		$diam_p = substr($pizza_model[0], 2, 2);
		$col_ur = substr($pizza_model[0], 5, 1);
		$varmodeldiam_array[intval($diam_p)][intval($col_p)] = intval($col_ur);
	}

	function cmp($a, $b)
	{
		if ($a == $b) {
			return 0;
		}
		return ($a > $b) ? -1 : 1;
	}
	uksort($varmodel_array, "cmp");

?>

<link rel="stylesheet" href="<?=$this->GetFolder()?>/css/simple-hint.css">
<script data-skip-moving=true src="<?=$this->GetFolder()?>/js/angular.min.js"></script>
<script data-skip-moving=true src="https://code.angularjs.org/1.3.0-rc.2/i18n/angular-locale_ru-ru.js"></script>
<script data-skip-moving=true src="<?=$this->GetFolder()?>/js/myApp.js"></script>
<script data-skip-moving=true src="<?=$this->GetFolder()?>/js/script.js"></script>
<script data-skip-moving=true>

	var app = angular.module('myTreeview');

	app.controller('myTreeviewCtrl', ['$scope', function ($scope) {
		//массив для запоминания выбора
		$scope.current_selection_arr = {};
		//массивы для полной стоимости
		$scope.full_price = {};
		$scope.trigger_price = {};
		//массив для hint'а
		$scope.opchecked = {};
		//массивы для диаграммы
		$scope.diag_srok_arr = {};
		$scope.diag_vid_arr = {};
		$scope.diag_risk_arr = {};
		//массивы комплектации и артикулов сборки
		$scope.complect_name = {};
		$scope.complect_articule = {};
		//модели select'ов
		$scope.checked_col = '0';
		$scope.checked_diam = '0';
		$scope.checked_urov = '1';
		$scope.searchComplect = '';
		//полная сумма
		$scope.summ_count = 0;
		//артикул
		$scope.sb_articule_code_placeholder = '';
		//диаграмма
		$scope.diag_srok = 0;
		$scope.diag_vid = 0;
		$scope.diag_risk = 0;
		//placeholder артикула
		$scope.sb_articule_code_placeholder = 'Введите артикул';
		//показ кнопки заказа
		$scope.zakaz_button = false;
		//массив регистра сведений
		$scope.rs_arr = [
			<?
			$str_rsarr = '';
			foreach ($rs_array as $pizza_model) {
				$str_rsarr = $str_rsarr."{ name: '".$pizza_model[0]."', code: [";
				foreach ($pizza_model[1] as $pizza_kod) {
					$str_rsarr = $str_rsarr."{ name: '".$pizza_kod."'},";
				}
				$str_rsarr = substr($str_rsarr,0,-1);
				$str_rsarr = $str_rsarr."]},";
			}
			$str_rsarr = substr($str_rsarr,0,-1);
			echo $str_rsarr;
			?>
		];
		//массив сборных кодов
		$scope.sbcode_arr = [
			<?
			//выбор артикулов всех вариаций печей
			$rs = CIBlockElement::GetList(
				array(),
				array(
					"IBLOCK_ID" => 54,
					array()
				),
				false,
				false,
				array("ID","NAME","PROPERTY_UF_CODE1","PROPERTY_UF_CODE2","PROPERTY_UF_CODE3",
					"PROPERTY_UF_CODE4","PROPERTY_UF_CODE5","PROPERTY_UF_CODE6",
					"PROPERTY_UF_CODE7","PROPERTY_UF_CODE8","PROPERTY_UF_CODE9")
			);
			$articule_sbor_code_count = 0;
			while($ar = $rs->GetNext()) {
				$str_sbcodearr = '';
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["NAME"]."', code: [";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE1_VALUE"]."'},";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE2_VALUE"]."'},";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE3_VALUE"]."'},";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE4_VALUE"]."'},";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE5_VALUE"]."'},";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE6_VALUE"]."'},";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE7_VALUE"]."'},";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE8_VALUE"]."'},";
				$str_sbcodearr = $str_sbcodearr."{ name: '".$ar["PROPERTY_UF_CODE9_VALUE"]."'}";
				$str_sbcodearr = $str_sbcodearr."] },\n ";
				echo $str_sbcodearr;
				$articule_sbor_code_count++;
			}
			//$str_sbcodearr = substr($str_rsarr,0,-1);
			?>
		];
		//массив деталей
		$scope.items = [
		<?
		//формируем json из изделий
		if (count($arResult["ITEMS"]) > 0) {
			$prev_level = 1;
			$sort_input = 1;
			$id_input = 0;
			$id_top_uroven = 0;

			foreach ($arResult['ITEMS'] as $key => $arItem) {

				if ($prev_level == 1) {
					$id_input = 0;
				}
				if ($arItem['DEPTH_LEVEL'] == 1) {
					$id_top_uroven++;
				}

				if ($prev_level > $arItem['DEPTH_LEVEL']) {
					$levels = $prev_level - $arItem['DEPTH_LEVEL'];
					echo '] }';
					$id_input++;
					for ($i = 0; $i < $levels; $i++) {
						echo "] },";
					}
				}
				?>

				{ name_origin: '<? echo $arItem['NAME'];?>',

				<?
					//свойставо "артикул"
					$db_props = CIBlockElement::GetProperty(53, $arItem['ID'], array("sort" => "asc"), Array("CODE"=> '')); // XXX - множественное свойства типа "Строка"
					$val_prop = [];
					while ($ob = $db_props->GetNext())
					{
						if ($ob['VALUE']) {
							$val_prop[$ob['CODE']] = $ob['VALUE'];
						}
					}

					//свойставо "модели"
					if ($val_prop["CML2_ARTICLE"]) {
						echo " articule: '".$val_prop["CML2_ARTICLE"]."',";
						echo " kod: '".$val_prop["CML2_TRAITS"]."',";
						$model_str = '';
						foreach ($rs_array as $pizza_model) {
							foreach ($pizza_model[1] as $artl) {
								if ($artl == $val_prop["CML2_TRAITS"]) {
									$model_str = $model_str.'mod_'.$pizza_model[0].' ';
								}
							}
						}

						echo " modelcomp: '".$model_str."',";
						//свойстава "коэффициенты_срок_службы"
						if ($val_prop["KOEFFITSIENT_SROK_SLUZHBY"]) {
							echo " srok: '".$val_prop["KOEFFITSIENT_SROK_SLUZHBY"]."',";
						} else {
							echo " srok: '1',";
						}
						//свойстава "коэффициенты_внешний_вид"
						if ($val_prop["KOEFFITSIENT_VNESHNIY_VID"]) {
							echo " vid: '".$val_prop["KOEFFITSIENT_VNESHNIY_VID"]."',";
						} else {
							echo " vid: '1',";
						}
						//свойстава "коэффициенты_логистические риски"
						if ($val_prop["KOEFFITSIENT_LOGISTICHESKIE_RISKI"]) {
							echo " risk: '".$val_prop["KOEFFITSIENT_LOGISTICHESKIE_RISKI"]."',";
						} else {
							echo " risk: '0.01',";
						}

						if ($val_prop["KOMPLEKTUYUSHCHIE_GABARITY_PECHI"]) {
							echo " gabpechi: '".$val_prop["KOMPLEKTUYUSHCHIE_GABARITY_PECHI"]."',";
						}
						if ($val_prop["KOMPLEKTUYUSHCHIE_GABARITY_KAMERY"]) {
							echo " gabkamera: '".$val_prop["KOMPLEKTUYUSHCHIE_GABARITY_KAMERY"]."',";
						}
						if ($val_prop["KOMPLEKTUYUSHCHIE_POTREB_MOSHCHNOST"]) {
							echo " potrmosh: '".$val_prop["KOMPLEKTUYUSHCHIE_POTREB_MOSHCHNOST"]."',";
						}
						if ($val_prop["KOMPLEKTUYUSHCHIE_SRED_POTREBLENIE"]) {
							echo " srmosh: '".$val_prop["KOMPLEKTUYUSHCHIE_SRED_POTREBLENIE"]."',";
						}
						if ($val_prop["KOMPLEKTUYUSHCHIE_MASSA"]) {
							echo " massa: '".$val_prop["KOMPLEKTUYUSHCHIE_MASSA"]."',";
						}
					}
					//свойставо "цена" и тд
					$ar_res = CPrice::GetBasePrice($arItem['ID']);
					if ($ar_res['PRICE']) {
						echo " price: '".$ar_res['PRICE']."',";
						echo " input: 'name_".$id_top_uroven.'_'.$id_input."_".$arItem['DEPTH_LEVEL']."',";
						echo " inputbool: 'true',";
						echo " myhintclass: 'hint-left-t-notice hint-fade',";
					}
				?>
				name: '<?
					if ($val_prop["KOMPLEKTUYUSHCHIE_OPISANIE"]) {
						echo $val_prop["KOMPLEKTUYUSHCHIE_OPISANIE"];
						//echo $arItem['NAME'];
					} else {
						echo $arItem['NAME'];
					}
					?>',
				sortinput: '<?=$sort_input?>',
				depth: '<?=$arItem['DEPTH_LEVEL']?>',
				numberizd: '<?=$id_top_uroven?>',
				childrens: [ <?if($arParams['COUNT_ELEMENTS'] != "N" && $arItem['TYPE'] == "S"):?> (<?=$arItem['ELEMENT_CNT']?>)<?endif?>
				<?
					if ($arResult['ITEMS'][$key + 1]['DEPTH_LEVEL'] == $arItem['DEPTH_LEVEL']) {
						echo "] },";
					}
				$prev_level = $arItem['DEPTH_LEVEL'];
				$sort_input++;
				unset($arItem);
			}

            for ($i = 0; $i < $prev_level; $i++) {
				echo "] }";
			}
		}
		?>
		];

		$scope.init_app = function () {
			$('.main_frame_optimize').css("display","block");
			$('.standart_options').css({"margin-left": ($(window).width() - 900)/2 + "px"});
			setTimeout(function() {
				$scope.reload_modificate();
				$('.sbor_model_code').blur();
			}, 100);
		};

		//выбор детали
		$scope.click_summ = function (cost, keyinp, sortinput, srok, vid, risk) {
			$('.change_complect input').removeAttr('checked');
			$scope.summ(cost, keyinp, sortinput, srok, vid, risk);
		};

		//суммирование общей стоимости
		$scope.summ = function (cost, keyinp, sortinput, srok, vid, risk) {
			$scope.summ_count = 0;
			if ($scope.trigger_price[keyinp] != sortinput) {
				if (keyinp == 'name_6_3_2') {
					index_curr = $('li[data-sort="'+sortinput+'"]').index();
				} else {
					index_curr = $('li[data-sort="'+sortinput+'"]').index() + 1;
					if (keyinp == 'name_6_1_4' && index_curr != 1) index_curr = 2;
				}
				$scope.current_selection_arr[keyinp] = index_curr;
				$scope.trigger_price[keyinp] = parseInt(sortinput);
				$scope.full_price[keyinp] = parseFloat(cost);
				//сборка артикулов и названий
				$('.expert_options input[name="'+ keyinp + '"]').each(function(indx, element){
					if ($(element).is(':checked')) {
						$scope.complect_name[keyinp] = $(element).attr('data-text');
						$scope.complect_articule[keyinp] = $(element).attr('data-kod');
					}
				});
				$('li#li_id_'+ sortinput).parent('ul').children('li').each(function(indx, element){
					$scope.opchecked[$(this).attr('id')] = '';
				});
				$scope.opchecked['li_id_'+sortinput] = 'hint-opnulchecked';
				$('.depth_1 input[name='+ keyinp +']').parent().parent().parent().children('span').addClass('checked_punkt');
			} else {
				$scope.current_selection_arr[keyinp] = 0;
				$scope.trigger_price[keyinp] = -1;
				$scope.full_price[keyinp] = 0;
				$scope.complect_name[keyinp] = '';
				$scope.complect_articule[keyinp] = '';
				$scope.opchecked['li_id_'+sortinput] = '';
				$('.depth_1 input[name='+ keyinp +']').removeAttr("checked");
				$('.depth_1 input[name='+ keyinp +']').parent().parent().parent().children('span').removeClass('checked_punkt');
			}

			for (var key in $scope.full_price) {
				$scope.summ_count = $scope.summ_count + $scope.full_price[key];
			}
			if ($scope.summ_count == 0) {
				$scope.full_price = {};
				$scope.trigger_price = {};
			}
			//alert($scope.summ_count + ' - ' + $scope.min_price_count);
			$('.bx_filter_param_label').removeClass('checked_compl');
			if ($scope.min_price_count == $scope.summ_count) {
				$('#arrFilterPizza_varstoimostlabel1').addClass('checked_compl');
			}
			if ($scope.optimal_price_count == $scope.summ_count) {
				$('#arrFilterPizza_varstoimostlabel2').addClass('checked_compl');
			}
			if ($scope.max_price_count == $scope.summ_count) {
				$('#arrFilterPizza_varstoimostlabel3').addClass('checked_compl');
			}
			$scope.reload_modificate();
			$scope.diag_recount(srok, vid, risk, keyinp);
			$scope.forming_code();
		};

		//кнопка сброс
		$scope.reset_all = function () {
			$scope.searchComplect = 'mod_0-0/1';
			$scope.checked_col = '0';
			$scope.checked_diam = '0';
			$scope.checked_urov = '1';
			$('.change_type input').removeClass('checked_item');
			$('.change_complect input').removeAttr('checked');
			$scope.diag_srok = 0;
			$scope.diag_vid = 0;
			$scope.diag_risk = 0;
			$scope.sb_articule_code = '';
			$scope.zakaz_button = false;
			$('.expert_options').addClass('rb');
			$('.standart_options').addClass('center_mod').css({"margin-left": ($(window).width() - 900)/2 + "px"});
			$('#gabpechi').text("");
			$('#gabkamera').text("");
			$('#potrmosh').text("");
			$('#srmosh').text("");
			$('#massa').text("");
			$scope.current_selection_arr = {};
			$scope.summ_del();
		};

		//отчистка массивов построения цены и обнуление цены
		$scope.summ_del = function () {
			$scope.summ_count = 0;
			$scope.full_price = {};
			$scope.trigger_price = {};
			$scope.opchecked = {};
			$('input#name_6_1_4_1000').parent().addClass("hint-opnulchecked");
			$('.bx_filter_param_label').removeClass('checked_compl');
			$('.depth_1 input[type="radio"]').removeAttr("checked");
			$('.depth_1 span').removeClass('checked_punkt');
			setTimeout(function() {
				$scope.reload_modificate();
			}, 100);
		};

		//просчет минимальной комплектации
		$scope.min_price = function () {
			$scope.array_inputname = {};
			$('.treeview input').each(function(indx, element){
				$scope.array_inputname[$(element).attr('name')] = true;
			});
			for (var key in $scope.array_inputname) {
				var min_cost = 1000000;
				var index_cost = 0;
				var srok = 0;
				var vid = 0;
				var risk = 0;
				$('ul').children('li').children('input[name="'+key+'"]').each(function(indx1, element1){
					if (!$(element1).is(':disabled')) {
						if (parseInt($(element1).val()) < min_cost) {
							min_cost = $(element1).val();
							index_cost = $(element1).attr('data-sort');
							srok = $(element1).attr('data-srok');
							vid = $(element1).attr('data-vid');
							risk = $(element1).attr('data-risk');
						}
					}
				});
				if (!$('ul').children('li').children('input[data-sort="'+index_cost+'"]').is(':checked')) {
					$('ul').children('li').children('input[data-sort="'+index_cost+'"]').attr("checked","checked");
					$scope.summ(min_cost, key, index_cost, srok, vid, risk);
				}
			}
			$('.bx_filter_param_label').removeClass('checked_compl');
			$('#arrFilterPizza_varstoimostlabel1').addClass('checked_compl');
			$scope.min_price_count = $scope.summ_count;
		};

		//просчет оптимальной комплектации
		$scope.optimal_price = function () {
			$scope.array_inputname = {};
			$('.treeview input').each(function(indx, element){
				$scope.array_inputname[$(element).attr('name')] = true;
			});
			for (var key in $scope.array_inputname) {
				var min_cost = 1000000;
				var index_cost = 0;
				var srok = 0;
				var vid = 0;
				var risk = 0;
				$('ul').children('li').children('input[name="'+key+'"]').each(function(indx1, element1){
					if (!$(element1).is(':disabled')) {
						min_cost = $(element1).val();
						index_cost = $(element1).attr('data-sort');
						srok = $(element1).attr('data-srok');
						vid = $(element1).attr('data-vid');
						risk = $(element1).attr('data-risk');
						if (($(element1).attr('data-sort') != 75) && ($(element1).attr('data-sort') != 80) && (!($(element1).attr('data-sort') >= 28 && $(element1).attr('data-sort') <= 33))  && (!($(element1).attr('data-sort') >= 2 && $(element1).attr('data-sort') <= 7))) {
							return false;
						}
					}
				});
				if (!$('ul').children('li').children('input[data-sort="'+index_cost+'"]').is(':checked')) {
					$('ul').children('li').children('input[data-sort="'+index_cost+'"]').attr("checked","checked");
					$scope.summ(min_cost, key, index_cost, srok, vid, risk);
				}
			}
			$('.bx_filter_param_label').removeClass('checked_compl');
			$('#arrFilterPizza_varstoimostlabel2').addClass('checked_compl');
			$scope.optimal_price_count = $scope.summ_count;
		};

		//просчет максимальной комплектации
		$scope.max_price = function () {
			$scope.array_inputname = {};
			$('.treeview input').each(function(indx, element){
				$scope.array_inputname[$(element).attr('name')] = true;
			});
			for (var key in $scope.array_inputname) {
				var max_cost = 0;
				var index_cost = 0;
				var srok = 0;
				var vid = 0;
				var risk = 0;
				$('ul').children('li').children('input[name="'+key+'"]').each(function(indx1, element1){
					if (!$(element1).is(':disabled')) {
						if (parseInt($(element1).val()) > max_cost) {
							max_cost = $(element1).val();
							index_cost = $(element1).attr('data-sort');
							srok = $(element1).attr('data-srok');
							vid = $(element1).attr('data-vid');
							risk = $(element1).attr('data-risk');
						}
					}
				});
				if (!$('ul').children('li').children('input[data-sort="'+index_cost+'"]').is(':checked')) {
					$('ul').children('li').children('input[data-sort="'+index_cost+'"]').attr("checked", "checked");
					$scope.summ(max_cost, key, index_cost, srok, vid, risk);
				}
			}
			$('.bx_filter_param_label').removeClass('checked_compl');
			$('#arrFilterPizza_varstoimostlabel3').addClass('checked_compl');
			$scope.max_price_count = $scope.summ_count;
		};

		//Подсчет количества модификаций
		$scope.reload_modificate = function () {
			$scope.array_inputname = {};
			var col_check_item = 0;

			//метки деактивирующая печи с электронным управлением (при появлении электронного управления убрать)
			$('input[data-text="Электронное"]').attr("disabled","disabled");
			$('input[data-text="Электронное"]').parent().addClass("hint-opnulchecked");

			//выбрано электронное управление (необходимо доделать)
			if ($('input[data-text="Электронное управление"]').is(':checked')) {
				$('.depth_2.number_izd_6 ul li input').each(function(indx, element){
					if ($(element).is(':checked')) {
						$(element).removeAttr("checked");
					}
					$(element).attr("disabled","disabled");
					$scope.full_price[$(element).attr('name')] = 0;
				});
			} else {
				$('.depth_2.number_izd_6 ul li input').each(function(indx, element){
					$(element).removeAttr("disabled");
				});
			}

			$('input[type=radio]').each(function(indx, element){
				if ($(element).attr('name') != 'arrFilterPizza_varstoimost') {
					$scope.array_inputname[$(element).attr('name')] = true;
					if ($(element).is(':checked')) col_check_item++;
				}
			});
			item_iter = 0;
			for (var key in $scope.array_inputname) {
				item_iter++;
			}
			//расчет доступных модификаций
			if (item_iter <= 3) {
				$scope.count_mod = 384;
			} else {
				$scope.count_mod = 1;
			}
			for (var key in $scope.array_inputname) {
				var temp_mod = 0;
				$('input[name="'+key+'"]').each(function(indx1, element1){
					if ($(element1).is(':checked')) {
						temp_mod = 1;
						return false;
					} else {
						if (!$(element1).is(':disabled')) {
							temp_mod = temp_mod + 1;
						}
					}
				});
				$scope.count_mod = $scope.count_mod * temp_mod;
			}
			if ($scope.count_mod == 0) {
				$scope.count_mod = <?=$articule_sbor_code_count?>;
			}
			$('#modef_num').text($scope.count_mod);
			//проверка ввыбора всех комплектующих
			if ((col_check_item == item_iter)&&($scope.count_mod == 1)) {
				$scope.zakaz_button = true;
			} else {
				$scope.zakaz_button = false;
			}
			$scope.modelName = $scope.searchComplect.substr(4,$scope.searchComplect.length - 4);
			if ($scope.modelName == '1-40/1') $scope.modelName = '4-20/1';
			if ($scope.modelName == '1-50/1') $scope.modelName = '4-25/1';
			if ($scope.modelName == '1-60/1') $scope.modelName = '4-30/1';
			if ($scope.modelName == '1-70/1') $scope.modelName = '4-35/1';
		};

		$scope.change_col_pizza = function (id) {
			$scope.summ_del();
			var selcompl = '';
			$('.change_complect input').each(function(indx, element){
				if ($(element).is(':checked'))  {
					selcompl = $(element).attr("id");
				}
			});
			$('.change_complect input').removeAttr('checked');
			$scope.diag_srok = 0;
			$scope.diag_vid = 0;
			$scope.diag_risk = 0;

			//setTimeout(function() {
				$('.change_col input').each(function(indx, element){
					//alert($(element).attr('id') + ' - ' + id);
					if ($(element).attr('id') == id) {
						if ($(element).hasClass('checked_item')) {
							$scope.checked_col = '0';
							$(element).removeClass('checked_item');
							$('#gabpechi').text("");
							$('#gabkamera').text("");
							$('#potrmosh').text("");
							$('#srmosh').text("");
							$('#massa').text("");
						} else {
							$('.change_col input').removeClass('checked_item');
							$(element).addClass('checked_item');
						}
					}
				});
				$('.change_diam input').each(function(indx, element){
					if ($(element).attr('id') == id) {
						if ($(element).hasClass('checked_item')) {
							$scope.checked_diam = '0';
							$(element).removeClass('checked_item');
							$('#gabpechi').text("");
							$('#gabkamera').text("");
							$('#potrmosh').text("");
							$('#srmosh').text("");
							$('#massa').text("");
						} else {
							$('.change_diam input').removeClass('checked_item');
							$(element).addClass('checked_item');
						}
					}
					if(($(element).is(':checked'))&&($(element).is(':disabled'))) {
						$scope.checked_diam = '0';
					}
				});

				$('.sbor_model_code').change();

				/*
				$('.treeview input').removeAttr("checked");
				$('.treeview label').removeClass('checked_label');
				$('.depth_1 input').each(function(indx, element){
					if($(element).is(':checked')) {
						$(this).click();
					}
				});
				*/

				$('.depth_1 span').removeClass('checked_punkt');
				$('.depth_1:last-child').css({"height":$('.treeview').height() + "px"});



				$scope.reload_modificate();

			setTimeout(function() {
				$scope.set_current_selection();
				$('#sb_code_input').click();
				$('#'+selcompl).attr("checked","checked");
				//выбор характеристик печи
				$('#gabpechi').text($('.depth_1.number_izd_1 input:first-child').attr("data-gabpechi"));
				$('#gabkamera').text($('.depth_1.number_izd_2 input:first-child').attr("data-gabkamera"));
				$('#potrmosh').text($('.depth_1.number_izd_4 input:first-child').attr("data-potrmosh"));
				$('#srmosh').text($('.depth_1.number_izd_4 input:first-child').attr("data-srmosh"));
				$('#massa').text($('.depth_1.number_izd_1 input:first-child').attr("data-massa"));
				if ($('.expert_options').height() < 100) {
					$('.expert_options').addClass('rb');
					$('.standart_options').addClass('center_mod').css({"margin-left": ($(window).width() - 900)/2 + "px"});
				}
			}, 100);
		};


		$scope.diag_recount = function (srok, vid, risk, keyinp) {
			$scope.diag_srok_arr[keyinp] = parseFloat(srok);
			$scope.diag_srok = 0;
			iter = 0;
			$scope.diag_srok_tmp = '';
			for (var key in $scope.diag_srok_arr) {
				$scope.diag_srok_tmp = $scope.diag_srok_tmp + " + " + $scope.diag_srok_arr[key];
				if (!$scope.diag_srok_arr[key]) $scope.diag_srok_arr[key] = 1;
				$scope.diag_srok = $scope.diag_srok + $scope.diag_srok_arr[key]*100;
				iter++;
			}
			//alert($scope.diag_srok_tmp);
			$scope.diag_srok = $scope.diag_srok/iter;

			$scope.diag_vid_arr[keyinp] = parseFloat(vid);
			$scope.diag_vid = 0;
			iter = 0;
			for (var key in $scope.diag_vid_arr) {
				if (!$scope.diag_vid_arr[key]) $scope.diag_vid_arr[key] = 1;
				$scope.diag_vid = $scope.diag_vid + $scope.diag_vid_arr[key]*100;
				iter++;
			}
			$scope.diag_vid = $scope.diag_vid/iter;

			$scope.diag_risk_arr[keyinp] = parseFloat(risk);
			$scope.diag_risk = 1;
			for (var key in $scope.diag_vid_arr) {
				if (!$scope.diag_risk_arr[key]) $scope.diag_risk_arr[key] = 0.01;
				$scope.diag_risk = $scope.diag_risk + $scope.diag_risk_arr[key]*100;
				iter++;
			}
			$scope.diag_risk = $scope.diag_risk/iter;
			if ($scope.diag_risk > 5) $scope.diag_risk = 50;
		};

		$scope.expert_mod_trigger = function() {
			if ($('.expert_options').hasClass('rb')) {
				$('.expert_options').removeClass('rb');
				$('.standart_options').removeClass('center_mod').css({"margin-left":"0"});
			} else {
				$('.expert_options').addClass('rb');
				$('.standart_options').addClass('center_mod').css({"margin-left": ($(window).width() - 900)/2 + "px"});
			}
		};

		$scope.select_code = function() {
			//создание массива кодов выбранного артикла
			$scope.selcode = {};
			for (var key in $scope.sbcode_arr) {
				if (($scope.sb_articule_code)&&($scope.sb_articule_code.toUpperCase() == $scope.sbcode_arr[key]['name'].toUpperCase())) {
					$scope.selcode = $scope.sbcode_arr[key]['code'];
					break;
				}
			}
			//сравнение со $scope.rs_arr
			for (var key in $scope.rs_arr) {
				iter_count_sd = 0;
				iter_count_notsd = 0;
				iter_count_bool = true;
				for (var key1 in $scope.selcode) {
					if ($scope.selcode[key1]['name'] != 'нет') {
						$scope.rs_arr_tmp = $scope.rs_arr[key]['code'];
						for (var key2 in $scope.rs_arr_tmp) {
							if ($scope.rs_arr_tmp[key2]['name'] == $scope.selcode[key1]['name']) {
								iter_count_sd = iter_count_sd + 1;
							}
						}
					} else {
						iter_count_sd = iter_count_sd + 1;
						//метка деактивирующая артикулы печей с электронным управлением (при появлении электронного управления убрать)
						if (iter_count_notsd > 0) {
							iter_count_bool = false;
						}
						iter_count_notsd = iter_count_notsd + 1;
					}
				}

				if (($scope.selcode.length == iter_count_sd)&&(iter_count_bool)) {
					//сброс всего
					$scope.sb_articule_code_placeholder = $scope.sb_articule_code;
					$scope.sb_articule_code = '';
					$scope.reset_all();
					//выбор размера печи
					$scope.checked_col = $scope.rs_arr[key]['name'].substr(0,1);
					$('.change_col input[value="' + $scope.rs_arr[key]['name'].substr(0,1) + '"]').addClass('checked_item');
					$scope.checked_diam = $scope.rs_arr[key]['name'].substr(2,2);
					$('.change_diam input[value="' + $scope.rs_arr[key]['name'].substr(2,2) + '"]').addClass('checked_item');
					$scope.searchComplect = 'mod_' + $scope.rs_arr[key]['name'];
					//проставление опций
					setTimeout(function() {
						for (var key1 in $scope.selcode) {
							var index_cost = $('input[data-kod="'+ $scope.selcode[key1]['name'] +'"]').attr('data-sort');
							var srok = $('input[data-kod="'+ $scope.selcode[key1]['name'] +'"]').attr('data-srok');
							var vid = $('input[data-kod="'+ $scope.selcode[key1]['name'] +'"]').attr('data-vid');
							var risk = $('input[data-kod="'+ $scope.selcode[key1]['name'] +'"]').attr('data-risk');
							var input_name = $('input[data-kod="'+ $scope.selcode[key1]['name'] +'"]').attr('name');
							var input_cost = $('input[data-kod="'+ $scope.selcode[key1]['name'] +'"]').attr('value');
							if (!$('input[data-kod="'+ $scope.selcode[key1]['name'] +'"]').is(':checked')) {
								$('input[data-kod="'+ $scope.selcode[key1]['name'] +'"]').attr("checked", "checked");
								$scope.summ(input_cost, input_name, index_cost, srok, vid, risk);
							}
							$scope.diag_recount(srok, vid, risk, input_name);
						}
						//если нет термомерта
						if (!$('input[name="name_6_1_4"]:first-child').is(':checked')) {
							var index_cost = $('input#name_6_1_4_1000').attr('data-sort');
							var srok = $('input#name_6_1_4_1000').attr('data-srok');
							var vid = $('input#name_6_1_4_1000').attr('data-vid');
							var risk = $('input#name_6_1_4_1000').attr('data-risk');
							var input_name = $('input#name_6_1_4_1000').attr('name');
							var input_cost = $('input#name_6_1_4_1000').attr('value');
							$('input#name_6_1_4_1000').attr("checked", "checked");
							$scope.summ(input_cost, input_name, index_cost, srok, vid, risk);
						}

						//выбор характеристик печи
						$('#gabpechi').text($('.depth_1.number_izd_1 input:first-child').attr("data-gabpechi"));
						$('#gabkamera').text($('.depth_1.number_izd_2 input:first-child').attr("data-gabkamera"));
						$('#potrmosh').text($('.depth_1.number_izd_4 input:first-child').attr("data-potrmosh"));
						$('#srmosh').text($('.depth_1.number_izd_4 input:first-child').attr("data-srmosh"));
						$('#massa').text($('.depth_1.number_izd_1 input:first-child').attr("data-massa"));

						//$('.bx_filter_top_line input[type=text]').blur();
						$('.expert_options_button').click();
					}, 100);


					//$scope.expert_mod_trigger();
					//пересчет количества модификаций
					$scope.reload_modificate();
					break;
				} else {
					$scope.sb_articule_code_placeholder = 'артикула не существует';
					$('#sb_code_input').val('');
				}
			}
		};

		$scope.forming_code = function() {
			formingcode = [];
			tmp_name = '';
			serialize_formingcode = '';
			$('.treeview input').each(function(indx, element){
				if ($(element).is(':checked')) {
					if ($(element).attr('data-kod')) formingcode.push($(element).attr('data-kod'));
				}
			});
			formingcode.sort();
			for (var key1 in formingcode) {
				serialize_formingcode = serialize_formingcode + '-' + formingcode[key1];
			}
			for (var key in $scope.sbcode_arr) {
				tmp_sbcode_arr = [];
				for (var key1 in $scope.sbcode_arr[key]['code']) {
						tmp_sbcode_arr.push($scope.sbcode_arr[key]['code'][key1]['name']);
				}
				for (var key1 in tmp_sbcode_arr) {
					if (tmp_sbcode_arr[key1] == 'нет') {
						tmp_sbcode_arr.splice(key1,1);
					}
				}
				if (tmp_sbcode_arr.length == formingcode.length) {
					//сортируем массивы по возрастанию кодов
					tmp_sbcode_arr.sort();
					//сериализация массивов
					serialize_sbcode = '';
					for (var key1 in tmp_sbcode_arr) {
						serialize_sbcode = serialize_sbcode + '-' + tmp_sbcode_arr[key1];
					}
					if (serialize_sbcode.toString() == serialize_formingcode.toString()) {
						tmp_name = $scope.sbcode_arr[key]['name'];
						break;
					}
				}
			}
			$scope.sb_articule_code_placeholder = 'Введите артикул';
			$scope.sb_articule_code = tmp_name;
		};
		$scope.forming_code_input = function() {
			if (($scope.sb_articule_code == '')||(!$scope.sb_articule_code)) {
				$scope.sb_articule_code = 'SB';
			}
		};

		$scope.set_current_selection = function() {
			for (var key in $scope.current_selection_arr) {
				var iter_cs = 0;
				//alert(key + ' = ' + $scope.current_selection_arr[key]);
				$('.treeview input[name="' + key + '"]').each(function (indx, element) {
					iter_cs = iter_cs + 1;
					if (($scope.current_selection_arr[key] == iter_cs)&&(!$(element).is(':disabled'))) {
						var index_cost = $(element).attr('data-sort');
						var srok = $(element).attr('data-srok');
						var vid = $(element).attr('data-vid');
						var risk = $(element).attr('data-risk');
						var input_name = key;
						var input_cost = $(element).attr('value');
						$(element).attr("checked", "checked");
						$scope.summ(input_cost, input_name, index_cost, srok, vid, risk);
						$scope.diag_recount(srok, vid, risk, input_name);
					}
				});
			}
		};

		$scope.get_zakaz = function() {
			if ($scope.zakaz_button) {
				$.ajax({
					url: '/catalog/include/optimizer_pizza_addblock.php/?code=STILLAG BRAVA ' + $scope.modelName + ' (' + $scope.sb_articule_code + ')&corp=' + $scope.complect_name["name_1_0_2"] + '&upak=' + $scope.complect_name["name_5_0_2"] + '&pod=' + $scope.complect_name["name_3_0_2"] + '&ten=' + $scope.complect_name["name_4_0_2"] + '&term=' + $scope.complect_name["name_6_1_4"] + '&nakl=' + $scope.complect_name["name_6_0_4"] + '&tt=' + $scope.complect_name["name_6_2_4"] + '&kamera=' + $scope.complect_name["name_2_0_2"] + '&fasad=' + $scope.complect_name["name_6_3_2"] + '&cost=' + $scope.summ_count,
					type: "GET",
					success: function () {
						document.location.href = "/forpartners/uc/cart/?back_link=" + location.pathname;
					}
				});
			}
		}

	} ])

</script>

<div ng-app="myTreeview">
	<div ng-controller="myTreeviewCtrl">
		<div class="bx_filter bx_horizontal" ng-init="init_app()">
			<div class="bx_filter_section" style="position: relative;">
				<div class="bx_filter_title_small"></div>
				<div class="col-md-12 col-sm-12 col-xs-12" style="position: relative;">
						<div class="bx_filter_top_line">
							<div class="bx_filter_title" style="float: left;"></div>
							<h4>Ассортимент</h4>
							<div class="bx_filter_popup_result left" id="modef" style="display: inline-block;">
								<span id="modef_num">0</span> модификаций печей
							</div>
						</div>
						<div class="sb_code_div">
							<h4>Подобрать по артикулу</h4>
							<input  ng-enter="select_code()" ng-click="forming_code_input()" id="sb_code_input" type="text" ng-model="sb_articule_code" placeholder="{{sb_articule_code_placeholder}}" maxlength="6">
							<button ng-click="select_code()">Показать</button>
						</div>
				</div>
				<div class="col-md-12 col-sm-12 col-xs-12 main_frame_optimize" style="position: relative; display: none;">
				<div class="col-md-6 col-sm-6 col-xs-12 width100_for_mob standart_options center_mod" style="position: static;">
					<div class="col-md-6 col-sm-6 col-xs-12 change_type">
						<div class="row">
							<div class="bx_filter_parameters_box active">
								<span class="bx_filter_container_modef"></span>
								<div class="bx_filter_block">
									<div class="bx_filter_parameters_box_container">
										<div class="bx_filter_select_container change_col" style="background: none; border: none; width: 100%;">
											<h5>Сколько пицц?</h5>
												<?
												$iter_opt = 1;
												foreach ($varmodel_array as $col_p => $diam_ur) {
													/*
													echo "<input ng-model='checked_col' name='inp_col_name' type='radio' id='inp_col_name".$iter_opt."' class='ch_item".$iter_opt."' ng-if='";
													$if_str = '';
													foreach ($diam_ur as $diam => $uroven) {
														$if_str = $if_str."(checked_diam == \"".$diam."\")||";
													}
													$if_str = substr($if_str, 0, -2);
													echo $if_str."' ng-value='".$col_p."' ng-click='change_col_pizza()'><label for='inp_col_name".$iter_opt."'>".$col_p." пиццы</label>";
													$iter_opt++;
													*/
													echo "<input ng-model='checked_col' name='inp_col_name' type='radio' id='inp_col_name".$iter_opt."' class='ch_item".$iter_opt."' ng-value='".$col_p."' ng-click='change_col_pizza(\"inp_col_name".$iter_opt."\")'><label for='inp_col_name".$iter_opt."'  id='col_p_label".$col_p."'>&nbsp;</label>";
													$iter_opt++;
												}
												?>

										</div>
									</div>
									<div class="clb"></div>
								</div>
							</div>


							<div class="bx_filter_parameters_box active">
								<span class="bx_filter_container_modef"></span>
								<div class="bx_filter_block">
									<div class="bx_filter_parameters_box_container">
										<div class="bx_filter_select_container change_diam" style="background: none; border: none; width: 100%;">
											<h5>Размер пиццы:</h5>
												<div style="width: 100%; display: block; overflow: hidden;">
												<?
												$iter_opt = 1;
												foreach ($varmodeldiam_array as $diam => $col_ur) {
													echo "<input ng-model='checked_diam' name='inp_diam_name' type='radio' id='inp_diam_name".$iter_opt."' class='ch_item".$iter_opt."' ng-disabled='";
													$if_str = '';
													foreach ($col_ur as $col_p => $uroven) {
														$if_str = $if_str."(checked_col == \"".$col_p."\")||";
													}
													$if_str = substr($if_str, 0, -2);
													$if_str = '!('.$if_str.')';
													echo $if_str."' value='".$diam."' ng-click='change_col_pizza(\"inp_diam_name".$iter_opt."\")'><label ng-disabled='".$if_str."' for='inp_diam_name".$iter_opt."'>".$diam." см</label>";
													$iter_opt++;
													//echo "<input ng-model='checked_diam' name='inp_diam_name' type='radio' id='inp_diam_name".$iter_opt."' class='ch_item".$iter_opt."' value='".$diam."' ng-click='change_col_pizza()'><label for='inp_diam_name".$iter_opt."'>".$diam." см</label>";
												    if ($iter_opt == 5) echo "</div><div style='width: 100%; display: block; overflow: hidden;'>";
												}
												?>
												</div>
										</div>
									</div>
									<div class="clb"></div>
								</div>
							</div>

							<div class="bx_filter_parameters_box active">
								<span class="bx_filter_container_modef"></span>
								<div class="bx_filter_block">
									<div class="bx_filter_parameters_box_container">
										<div class="bx_filter_select_container change_urov" style="background: none; border: none; width: 100%;">
											<h5>Количество уровней печи:</h5>
												<input ng-model='checked_urov' name="inp_urov_name" type="radio" id="inp_urov_name1" class="ch_item1" value="1" ><label for="inp_urov_name1">1 уровень</label>
												<input ng-model='checked_urov' name="inp_urov_name" type="radio" id="inp_urov_name2" class="ch_item2" value="2" disabled="disabled"><label for="inp_urov_name2">2 уровня</label>
										</div>
									</div>
									<div class="clb"></div>
								</div>
							</div>
							<input type="text" class="sbor_model_code" ng-model="searchComplect" style="display: none;" value="mod_{{checked_col}}-{{checked_diam}}/{{checked_urov}}">

							<div class="bx_filter_parameters_box active checked_price" style="position: relative;">
								<span class="bx_filter_container_modef"></span>
								<div class="bx_filter_block">
									<div class="bx_filter_parameters_box_container change_complect" style="background: none; border: none; width: 100%;">
										<h5 style="margin-bottom: 15px;">Особые комплектации:</h5>
										<input ng-disabled="(checked_col == 0)||(checked_diam == 0)" ng-click="min_price()" type="radio" name="arrFilterPizza_varstoimost" id="arrFilterPizza_varstoimost1"><label for="arrFilterPizza_varstoimost1">Fun</label>
										<input ng-disabled="(checked_col == 0)||(checked_diam == 0)" ng-click="optimal_price()" type="radio" name="arrFilterPizza_varstoimost" id="arrFilterPizza_varstoimost2"><label for="arrFilterPizza_varstoimost2" id="fun_plus">Fun Plus</label>
										<input ng-disabled="(checked_col == 0)||(checked_diam == 0)" ng-click="max_price()" type="radio" name="arrFilterPizza_varstoimost" id="arrFilterPizza_varstoimost3"><label for="arrFilterPizza_varstoimost3">Expert</label>
									</div>
									<div class="clb"></div>
								</div>
							</div>
							<div class="diagramma_pizza">
								<div class="diag_line" id="data-srok-line">&nbsp;&nbsp;Срок службы
									<div class="diag_percent_line" style="width: {{diag_srok}}%;">&nbsp;&nbsp;Срок службы</div>
								</div>
								<div class="diag_line" id="data-vid-line">&nbsp;&nbsp;Внешний вид
									<div class="diag_percent_line" style="width: {{diag_vid}}%;">&nbsp;&nbsp;Внешний вид</div>
								</div>
								<div class="diag_line" id="data-risk-line">&nbsp;&nbsp;Логистические риски
									<div class="diag_percent_line" style="width: {{diag_risk}}%;">&nbsp;&nbsp;Логистические риски</div>
								</div>
							</div>

						</div>
					</div>
					<div class="col-md-6 col-sm-6 col-xs-12 pizza_card">
						<div class="inner">
							<ul class="sbor_code_vanny">
								<li ng-if="(checked_col == 0)||(checked_diam == 0)" style="padding-left: 0;">Stillag Brava</li>
								<li ng-if="(checked_col != '0')&&(checked_diam != '0')" style="padding-left: 0;">Stillag Brava {{modelName}}</li>
							</ul>
							<div class="pizza_card_img" ng-click="forming_code()"></div>
							<div class="sbor_cost_div">
								<span class="apply_bottom_vanny" ng-if="(summ_count != 0)">{{summ_count | currency:'руб.':0}}</span>
								<span class="apply_bottom_vanny" style="font-size: 15px;" ng-if="(summ_count == 0)&&((checked_col == '0')||(checked_diam == '0'))">Выберите количество и размер выпекаемых пицц</span>
								<span class="apply_bottom_vanny" style="font-size: 15px;" ng-if="((summ_count == 0)&&((checked_col != '0')&&(checked_diam != '0')))">Выберите одну из представленных комплектаций или соберите печь в экспертных настройках</span>
							</div>
							<div class="pizza_card_prop">
								<table>
									<tr>
										<td>Габариты печи (ДхШхВ), мм</td>
										<td width="120px" id="gabpechi"></td>
									</tr>
									<tr>
										<td>Габариты камеры (ДхШхВ), мм</td>
										<td width="120px" id="gabkamera"></td>
									</tr>
									<tr>
										<td>Потреб. мощность, кВт</td>
										<td width="120px" id="potrmosh"></td>
									</tr>
									<tr>
										<td>Сред. потребление, кВт/час</td>
										<td width="120px" id="srmosh"></td>
									</tr>
									<tr>
										<td>Масса нетто, кг</td>
										<td width="120px" id="massa"></td>
									</tr>
								</table>
							</div>
							<div class="pizza_card_button">
								<button class="button_st zakaz_vanna_button" ng-click="get_zakaz()" ng-disabled="zakaz_button == false">Заказать</button>
								<button class="button_st filter_reset" ng-click="reset_all()">Отменить</button>
							</div>
						</div>
						</div>
					</div>
				<div class="col-md-6 col-sm-6 col-xs-12 expert_options rb" >
					<table width="100%" style="margin: 25px 0;">
						<tr>
							<td valign="top" style="width: 40px !important;" class="hide_mobile">
								<div class="expert_options_button" ng-click="expert_mod_trigger()" ng-if="(checked_col != '0')&&(checked_diam != '0')">
									<span>Экспертные настройки</span>
								</div>
							</td>
							<td style="padding-left: 15px;" class="plnull">
								<div style="position: relative;" >
									<div class="disable_box" ng-click="expert_mod_trigger()"></div>
									<ul my-treeview class="opac_tree">
										<li ng-repeat="item in items | filter:searchComplect" ng-if="(checked_col != '0')&&(checked_diam != '0')" my-treeview-childs="item.childrens" id="li_id_{{item.sortinput}}" data-index="id_{{item.articule}}" data-sort="{{item.sortinput}}" simple-hint="{{(item.price-full_price[item.input]) | formatHint}}"  ng-class="[item.myhintclass, opchecked['li_id_'+{{item.sortinput}}]]" class="depth_{{item.depth}} number_izd_{{item.numberizd}}">
											<input id="{{item.input}}_{{item.articule}}" type="radio" name="{{item.input}}" value="{{item.price}}" data-sort="{{item.sortinput}}" ng-if="item.inputbool" ng-click="click_summ(item.price, item.input, item.sortinput, item.srok, item.vid, item.risk)" data-srok="{{item.srok}}"  data-vid="{{item.vid}}"  data-risk="{{item.risk}}" data-text="{{item.name}}" data-gabpechi="{{item.gabpechi}}" data-gabkamera="{{item.gabkamera}}" data-potrmosh="{{item.potrmosh}}" data-srmosh="{{item.srmosh}}"  data-massa="{{item.massa}}" data-articule="{{item.articule}}" data-kod="{{item.kod}}"><label for="{{item.input}}_{{item.articule}}" ng-if="item.inputbool"><span style="display: none;">{{item.modelcomp}}</span>{{item.name}}</label><span ng-if="!item.inputbool">{{item.name}}</span>
											<br ng-if="item.input == 'name_6_1_4'"><input ng-if="item.input == 'name_6_1_4'" id="name_6_1_4_1000" type="radio" name="name_6_1_4" value="0" data-sort="1000" ng-if="item.inputbool" data-srok="1"  data-vid="0.55"  data-risk="0.01" ng-click="click_summ(0, 'name_6_1_4', 1000, 1, 0.55, 0.01)"  data-text="Без термометра" data-articule=""><label for="name_6_1_4_1000" ng-if="item.input == 'name_6_1_4'"><span style="display: none;">4-35/1 2-35/1</span>Без термометра</label>
										</li>
									</ul>
								</div>
							</td>
						</tr>
					</table>
				</div>

				</div>
			</div>
		</div>

	</div>
</div>



