<?php
#
#    Copyright 2011 Arnaud Betremieux <arno@arnoo.net>
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#

$REMFILE = '.reminders'; # reminders file
$OPTIONS = '-b1 -m'; # additional command line options to be passed systematically to remind
$BACKUPS = 12;  # numbers of backup to keep

$month = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('m');
$year = isset($_REQUEST['year']) ? $_REQUEST['year'] : date('Y');

if ($_SERVER['REQUEST_METHOD']=='GET')
	{
	if (isset($_GET['ajax']))
		{
		echo get_cal($month, $year);
		}
	else
		{
		build_page($month, $year);
		}
	}
else
	{
	check($_POST['reminders']);
	save($_POST['reminders']);
    echo get_cal($month, $year);
	}

function make_rem_date($month, $year)
	{
	return $year.'/'.$month.'/'.date('d');
	}

function get_cal($month, $year)
	{
	global $OPTIONS, $REMFILE;

	$date = make_rem_date($month, $year);

	return `remind -p $OPTIONS $REMFILE $date | rem2html --tableonly`;
	}

function check($reminders)
	{
	global $OPTIONS;

	$remout = `remind -dl -v $OPTIONS`;
	}

function save($reminders)
	{
	global $REMFILE, $BACKUPS;

	if ($BACKUPS>0)
		{
		copy($REMFILE, $REMFILE."_".date('Y-m-d_H-i-s'));
        }
    $backupfiles = glob($REMFILE.'_????-??-??_??-??-??');
    while (count($backupfiles) > $BACKUPS)
        {
        unlink(array_shift($backupfiles));
        }
	file_put_contents($REMFILE, $reminders);
	}

function build_page($month, $year)
	{
    global $REMFILE;

	echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
		<html>
		<head>
            <script type='text/javascript' src='jquery-1.4.4.min.js'></script>
			<script type='text/javascript'>

				var month = $month;
				var year = $year;

                function hotbox_on(name)  {}
                function hotbox_off(name) {}           

				function save_and_update()
					{
                    var params = {'reminders': $('#remeditor').val(),
                                  'month': month,
                                  'year': year};
					$.post('index.php', params, update_cal);
					}

				function find_line()
					{
					msg = $(this).text();
                    //$('#remeditor')
					}

                function init_cal()
					{
                    var cols = $('table[width=\"100%\"]');
                    $(cols[1]).click(month_prev);
                    $(cols[1]).css('cursor', 'pointer');
                    $(cols[2]).click(month_next);
                    $(cols[2]).css('cursor', 'pointer');
                    if ((month==".date('m').") &&
                        (year==".date('Y')."))
                        {highlight_today();}
                    }

                function update_cal(data)
                    {
                    $('.calendar').replaceWith($(data));
                    init_cal();
                    }

				function change_month(to_month, to_year)
					{
					month = to_month;
					year = to_year;
                    var params = {'ajax':true,
                                  'month':to_month,
                                  'year':to_year};
					$.get('', params, update_cal);
					}
	
				function month_next()
					{
                    var new_month = month+1;
                    var new_year = year;
                    if (new_month>12)
                        {
                        new_month = 1;
                        new_year += 1;
                        }
					change_month(new_month, new_year);
					}

				function month_prev()
					{
                    var new_month=month-1;
                    var new_year = year;
                    if (new_month==0)
                        {
                        new_month = 12;
                        new_year -= 1;
                        }
					change_month(new_month, new_year);
					}

                function highlight_today()
                    {
                    $('.numeral').each(function ()
                           {
                           if ($(this).text()==".date('d').")
                               {\$(this).text('today');}
                           }); 
                    }

				function init()
					{
					$('span.msg').click(find_line)
                                 .css('cursor','pointer');
					$('#save_and_update').click(save_and_update);
                    init_cal();
					}
				
				$(document).ready(init);
			</script>
			<style type='text/css'>
				#remeditor
					{
					width:		        100%;
					height:		        170px;
					}
                
                .caltable
                    {
                    border:             1px solid #000000;
                    border-collapse:    collapse;
                    }
			</style>
		</head>
		<body>";
	echo    get_cal($month, $year);
	echo "  <textarea id='remeditor'>";
	echo      file_get_contents($REMFILE);
	echo "  </textarea>
	        <button id='save_and_update'>Save and update</button>
	      </body>
	      </html>";
	}


# vim: set filetype=php expandtab tabstop=4 shiftwidth=4:
?>
