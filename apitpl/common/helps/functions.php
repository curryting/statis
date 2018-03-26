<?php
namespace common\helps;

/*
 * 自定义全局公共方法
 */
class functions{


	//指定字段排序
	    /**
	 * 二维数组根据字段进行排序
	 * @params array $array 需要排序的数组
	 * @params string $field 排序的字段
	 * @params string $sort 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
	 */
	public static function arraySort($array, $field, $sort = 'SORT_DESC')
	{
	    $arrSort = array();
	    foreach ($array as $uniqid => $row) {
	        foreach ($row as $key => $value) {
	            $arrSort[$key][$uniqid] = $value;
	        }
	    }
	    array_multisort($arrSort[$field], constant($sort), $array);
	    return $array;
	}
}




?>