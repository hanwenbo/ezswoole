<?php

namespace traits\model;
trait ConditionHelper
{

	/**
	 * 获得开始时间结束时间区间
	 * @param int $start_time
	 * @param int $end_time
	 * @return array
	 * @author 韩文博
	 */
	public function startEndTime( int $start_time, int $end_time  )
	{
		if( $start_time > 0 && $end_time > 0 ){
			$result = [['gt', $start_time], ['lt', $end_time]];
		} elseif( $start_time > 0 && $end_time == 0 ){
			$result = ['gt', $start_time];
		} elseif( $start_time == 0 && $end_time > 0 ){
			$result = ['lt', $end_time];
		} else{
			$result = [];
		}
		return $result;
	}


}
