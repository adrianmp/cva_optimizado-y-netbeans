<?php

		function getMonthDays($Month, $Year)
		{
		   //Si la extensión que mencioné está instalada, usamos esa.
		   if( is_callable("cal_days_in_month"))
		   {
		      return cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
		   }
		   else
		   {
		      //Lo hacemos a mi manera.
		      return date("d",mktime(0,0,0,$Month+1,0,$Year));
		   }
		}
		
		function day($years, $months, $days)
		{
			//dia Actual
			$year = date("Y");
			$month = date("m");
			$day = date("d");			
			//Calculo diferencias
			$y = $years - $year;
			$d = $days - $day;
			$m = $months - $month;
			$dm = getMonthDays($month, $year);
			$total = $dm - $day;
			//comparaciones
			if($y == 0)
			{
				if($m == 0)
				{
					if($d>=0 && $d<6)
						echo $year."-".$month."-".$day;
					else
					{
						if($d>5)
							echo $years."-".$months."-".$days." fecha - interval de 5";
					}
				}
				else 
				{
					if($m>0)
					{
						if($days >= 1 && $days < 6)
						{
							if($total > 5 && $total<31)
								echo $years."-".$months."-".$days." fecha - interval de 5";
							else
								echo $year."-".$month."-".$day;
						}
						else 
						{
							echo $years."-".$months."-".$days." fecha - interval de 5";		
						}
					}
				}
			}
			else 
			{
				if($m == 0)
				{
					if($d>=0 && $d<6)
						echo $year."-".$month."-".$day;
					else
					{
						if($d>5)
							echo $years."-".$months."-".$days." fecha - interval de 5";
					}
				}
				else 
				{
					if($m>0)
					{
						if($days >= 1 && $days < 6)
						{
							if($total > 5 && $total<31)
								echo $years."-".$months."-".$days." fecha - interval de 5";
							else
								echo $year."-".$month."-".$day;
						}
						else 
						{
							echo $years."-".$months."-".$days." fecha - interval de 5";		
						}
					}
					else 
					{
							if($days >= 1 && $days < 6)
							{
								if($total > 5 && $total<31)
									echo $years."-".$months."-".$days." fecha - interval de 5";
								else
									echo $year."-".$month."-".$day;
							}
							else 
							{
								echo $years."-".$months."-".$days." fecha - interval de 5";		
							}
					}
				}
			}
						
		}
		day("2013","03","15");
?>