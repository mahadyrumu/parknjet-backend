<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ReportQueries;
use ReportQueriesParams;

class ReportsService_old
{
    public function getSearchQuery($item, $week)
    {
        $week = preg_replace("/[^0-9]/", "", $week);
        $lot1_query1 = [];
        $lot2_query1 = [];

        switch ($item) {
            case "Lot Use Report":

                $q0 = str_replace('@wk', $week, ReportQueries::q_lot_use_20);
                $q1 = str_replace('@wk', $week, ReportQueries::q_lot_use_21);

                $lot1_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::report_params, $week, 'lot1_mysql');
                $lot2_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::report_params, $week, 'lot2_mysql');

                break;

            case "Lot Use Self":

                $q0 = str_replace('@wk', $week, ReportQueries::q_lot_use_30);
                $q1 = str_replace('@wk', $week, ReportQueries::q_lot_use_31);

                $lot2_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::report_params, $week, 'lot2_mysql');

                break;

            case "Reservations":

                $q0 = str_replace('@wk', $week, ReportQueries::q_rsvn_0);
                $q1 = str_replace('@wk', $week, ReportQueries::q_rsvn);

                $lot1_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::rsvn_params, $week, 'lot1_mysql');
                $lot2_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::rsvn_params, $week, 'lot2_mysql');

                break;
            case "Drop Offs":

                $q0 = str_replace('@wk', $week, ReportQueries::q_do_0);
                $q1 = str_replace('@wk', $week, ReportQueries::q_do);

                $lot1_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::rsvn_params, $week, 'lot1_mysql');
                $lot2_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::rsvn_params, $week, 'lot2_mysql');

                break;
            case "Drop Off Self":

                $q0 = str_replace('@wk', $week, ReportQueries::q_do_s_0);
                $q1 = str_replace('@wk', $week, ReportQueries::q_do_s);

                // $lot1_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::rsvn_params, $week, 'lot1_mysql');
                $lot2_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::rsvn_params, $week, 'lot2_mysql');

                break;
            case "Pick Up Self":

                $q0 = str_replace('@wk', $week, ReportQueries::q_pu_s_0);
                $q1 = str_replace('@wk', $week, ReportQueries::q_pu_s);

                $lot1_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::rsvn_params, $week, 'lot1_mysql');
                $lot2_query1 = $this->get_report([$q0, $q1], ReportQueriesParams::rsvn_params, $week, 'lot2_mysql');

                break;
            case "Get Tpr W Pat":

                $lot1_query1 = $this->get_report([ReportQueries::q_tpr_w_pat_0, ReportQueries::q_tpr_w_pat], ReportQueriesParams::w_pat_params, $week, 'lot1_mysql');

                break;
            case "Get Res W Pat":

                $q0 = str_replace('@wk', $week, ReportQueries::q_pu_s_0);
                $q1 = str_replace('@wk', $week, ReportQueries::q_pu_s);

                $lot1_query1 = $this->get_report([ReportQueries::q_res_w_pat_0, ReportQueries::q_res_w_pat], ReportQueriesParams::w_pat_params, $week, 'lot1_mysql');
                $lot2_query1 = $this->get_report([ReportQueries::q_res_w_pat_0, ReportQueries::q_res_w_pat], ReportQueriesParams::w_pat_params, $week, 'lot2_mysql');

                break;

            default:
                Log::info("There is no query");
        }

        return [
            "lot1" => $lot1_query1,
            "lot2" => $lot2_query1
        ];
    }


    public function get_report($q, $params, $isFormatted = 1, $db_name)
    {
        try {
            if (is_array($q)) {
                $rows = DB::connection($db_name)
                    ->select($q[0]);

                $vars = $vals = [];

                foreach ($params as $k => $v) {
                    $vars[] = $k;
                    $vals[] = $rows[0]->{$v} ?? "NULL";
                }

                $qry = str_replace($vars, $vals, $q[1]);
            } else {
                $qry = $q;
            }

            $rows = DB::connection($db_name)
                ->select($qry);

            if ($isFormatted) {
                $rows = $this->format_data($rows);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $rows;
    }

    protected function format_data($rows)
    {
        $arrR = [];

        foreach ($rows as $row) {
            $calcArr = [$row->{'1'}, $row->{'2'}, $row->{'3'}, $row->{'4'}];

            $avg_val = $this->calculate_average($calcArr);
            $max_val = $this->calculate_max($calcArr);
            $max_per = round(100 * $row->{'0'} / $max_val, 0);
            $avg_per = round(100 * $row->{'0'} / $avg_val, 0);

            $row->{'max'} = $max_per;
            $row->{'avg'} = $avg_per;
            $zeroIndex = $row->{'0'} ? $row->{'0'} : 1;
            $avg_per = $avg_per ? $avg_per : 1;

            $row->{'style0'} = $this->get_style($row->{'0'}, ($zeroIndex * 100 / $avg_per));
            $row->{'style1'} = $this->get_style($row->{'1'}, ($zeroIndex * 100 / $avg_per));
            $row->{'style2'} = $this->get_style($row->{'2'}, ($zeroIndex * 100 / $avg_per));
            $row->{'style3'} = $this->get_style($row->{'3'}, ($zeroIndex * 100 / $avg_per));
            $row->{'style4'} = $this->get_style($row->{'4'}, ($zeroIndex * 100 / $avg_per));
            array_push($arrR, $row);
        }

        return $arrR;
    }

    protected function calculate_average($arr)
    {
        $value = 0;
        $i = 0;

        foreach ($arr as $v) {
            if ($v > 0) {
                $i++;
                $value += $v;
            }
        }

        if ($value > 0) {
            $avg = $value / $i;
        } else {
            $avg = 1;
        }

        return $avg;
    }

    protected function calculate_max($arr)
    {
        $max = 1;

        foreach ($arr as $v) {
            if ($v > $max) {
                $max = $v;
            }
        }
        return $max;
    }

    function get_style($val1, $val2)
    {
        $stl = "n0";
        $dv = floor(10 * ($val1 - $val2) / $val2);

        if ($dv >= 1) {
            if ($dv > 10) {
                $stl = "g9";
            } else {
                $stl = "g" . ($dv - 1);
            }
        } else if ($dv < -1) {
            if ($dv < -10) {
                $stl = "r9";
            } else {
                $stl = "r" . (abs($dv) - 2);
            }
        } else {
            $stl = "n0";
        }

        return $stl;
    }
}
