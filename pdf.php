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
			//fecha actual hoy
			date_default_timezone_set("America/Mexico_City");
			$hoy = explode ("-",date('Y-m-d'));
			$year = $hoy[0];
			$month = $hoy[1];
			$day = $hoy[2];
			echo $year."-".$month."-".$day."<br/>";			
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
					return ($d>=0 && $d<6)  ? '20': '22';
				}	
				else 
				{
					if($m>0)
					{
						if($days >= 1 && $days < 6)
							return ($total > 5 && $total<31) ? "23" : "24" ;
						else 
							return '1';
					}
				}
			}
			else 
			{
				if($m == 0)
					return ($d>=0 && $d<6) ? '25' : '26'; 
				else 
				{
					if($m>0)
					{
						if($days >= 1 && $days < 6)
							return ($total > 5 && $total<31) ? "27" : "28" ;
						else 
							return '1';
					}
					else 
					{
						if($days >= 1 && $days < 6)
							return ($total > 5 && $total<31) ? "29" : "30" ;
						else 
							return '1';
					}
				}
			}
			
						
		}
		echo day("2013","03","25");
		/*
		 if($y == 0)
			{
				if($m == 0)
				{
					ret($d>=0 && $d<6)
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
		 */
?>