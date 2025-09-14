<?php

class ReportQueries
{

    const L_pat_rsvn_lot1 = "SELECT
            ROUND(s0.w0 / s1.w1 * 100) '%',
            DATE_FORMAT(s0.dt, '%y-%m-%d') h01,
            LEFT(DATE_FORMAT(s0.dt, '%a'), 2) h02,
            s0.w0 '0',
            s1.w1 '1',
            s0.w0a '0a',
            s1.w1a '1a',
            ROUND(s0.w0 / s1.w1 * s1.w1a) 'P',
            DATE_FORMAT(s1.dt2, '%y-%m-%d') h11,
            LEFT(DATE_FORMAT(s1.dt2, '%a'), 2) h22
        FROM
            (SELECT
                DATE(t0.bookedDateTime) dt,
                    COUNT(id) w0a,
                    SUM(TIME(t0.bookedDateTime) <= TIME(NOW())) w0
            FROM
                reservation_info t0
            WHERE
                t0.isDeleted = 0 AND t0.id >= 251341
                    AND (t0.bookedDateTime BETWEEN @dt1 - INTERVAL 8 DAY AND @dt1 + INTERVAL 1 DAY)
            GROUP BY dt) s0
                LEFT JOIN
            (SELECT
                DATE(t1.bookedDateTime) dt2,
                    DATE(t1.bookedDateTime) + INTERVAL DATEDIFF(@dt1, @dt2) DAY dt,
                    COUNT(id) w1a,
                    SUM(TIME(t1.bookedDateTime) <= TIME(NOW())) w1
            FROM
                reservation_info t1
            WHERE
                t1.isDeleted = 0 AND t1.id >= 251341
                    AND (t1.bookedDateTime BETWEEN @dt2 - INTERVAL 8 DAY AND @dt2 + INTERVAL 1 DAY)
            GROUP BY dt) s1 ON s0.dt = s1.dt
        ORDER BY s0.dt DESC;
    ";

    const L1_pat_vehicle_count_lot1 = "SELECT 
            ROUND(SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt1) - 0 DAY)
                / SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt2) - 0 DAY) * 100) '%',
            DATE_FORMAT(s2.dt_1, '%y-%m-%d') h01,
            LEFT(DATE_FORMAT(s2.dt_1, '%a'), 2) h02,
            @c0:=SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt1) - 0 DAY) '0',
            @c1:=SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt2) - 0 DAY) '1',
            SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt) '0a',
            SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt) '1a',
            ROUND(SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt1) - 0 DAY)
                / SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt2) - 0 DAY) * SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt) ) 'P',    
            DATE_FORMAT(s2.dt_2, '%y-%m-%d') h11,
            LEFT(DATE_FORMAT(s2.dt_2, '%a'), 2) h12
        FROM
            (SELECT
                t1.do_dt, t1.pu_dt, t1.r_dt, t1.r_info_id
            FROM
                Lot1_dev_paul.reservations t1
            WHERE
                ((t1.do_dt <= @dt1 + INTERVAL 33 - 2 DAY
                    AND t1.pu_dt >= @dt1 - INTERVAL 2 DAY)
                    OR (t1.do_dt <= @dt2 + INTERVAL 33 - 2 DAY
                    AND t1.pu_dt >= @dt2 - INTERVAL 2 DAY)) UNION SELECT
                dropOffDateTime AS do_dt,
                    pickupDateTime pu_dt,
                    t3.bookedDateTime r_dt,
                    t3.id
            FROM
                reservation_info t3
            LEFT JOIN customer_activity t4 ON t3.id = t4.reservation_id
            WHERE
                (t4.claimId IS NULL OR t4.claimId = 0)
                    AND t3.isDeleted = 0
                    AND t3.dropOffDateTime BETWEEN NOW() - INTERVAL 2 HOUR AND @dt1 + INTERVAL 33 - 2 DAY) s1
                INNER JOIN
            (SELECT
                @dt1 + INTERVAL D + 2 - 2 DAY dt_1,
                @dt2 + INTERVAL D + 2 - 2 DAY dt_2,
                    @q:=CAST(CURDATE() + INTERVAL (D * 24) + 12 HOUR AS DATETIME) q,
                    CAST(@dt1 + INTERVAL (D * 24) + 12 HOUR AS DATETIME) q0,
                    CAST(@dt2 + INTERVAL (D * 24) + 12 HOUR AS DATETIME) q1
            FROM
                (SELECT
                A + B D
            FROM
                (SELECT - 2 A UNION ALL SELECT - 1 UNION ALL SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) A
            CROSS JOIN (SELECT 0 B UNION ALL SELECT 8 UNION ALL SELECT 16 UNION ALL SELECT 24) B) s0) s2 ON s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                OR s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
        WHERE
            s2.dt_1 IS NOT NULL
        GROUP BY s2.dt_1;

        ;


        --  Set @wk0 :=0;
        --  Set @wk1:=52;
        -- SELECT
        --     @dt1:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 0 DAY f_0,
        --     @dt2:=CURDATE() - INTERVAL 1 * 7 * @wk1 + 0 DAY f_1
    ";

    const L1_pat_vehicle_count_self_lot1 = "SELECT 
            ROUND(SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt1) - 0 DAY)
                / SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt2) - 0 DAY) * 100) '%',
            DATE_FORMAT(s2.dt_1, '%y-%m-%d') h01,
            LEFT(DATE_FORMAT(s2.dt_1, '%a'), 2) h02,
            @c0:=SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt1) - 0 DAY) '0',
            @c1:=SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt2) - 0 DAY) '1',
            SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt) '0a',
            SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt) '1a',
            ROUND(SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt1) - 0 DAY)
                / SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
                AND s1.r_dt <= NOW() - INTERVAL DATEDIFF(CURDATE(), @dt2) - 0 DAY) * SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt) ) 'P',    
            DATE_FORMAT(s2.dt_2, '%y-%m-%d') h11,
            LEFT(DATE_FORMAT(s2.dt_2, '%a'), 2) h12
        FROM
            (SELECT
                t1.do_dt, t1.pu_dt, t1.r_dt, t1.r_info_id
            FROM
                Lot1_dev_paul.reservations t1
            WHERE t1.parkingType = 'SELF' AND
                ((t1.do_dt <= @dt1 + INTERVAL 33 - 2 DAY
                    AND t1.pu_dt >= @dt1 - INTERVAL 2 DAY)
                    OR (t1.do_dt <= @dt2 + INTERVAL 33 - 2 DAY
                    AND t1.pu_dt >= @dt2 - INTERVAL 2 DAY)) UNION SELECT
                dropOffDateTime AS do_dt,
                    pickupDateTime pu_dt,
                    t3.bookedDateTime r_dt,
                    t3.id
            FROM
                reservation_info t3
            LEFT JOIN customer_activity t4 ON t3.id = t4.reservation_id
            WHERE
                (t4.claimId IS NULL OR t4.claimId = 0)
                    AND t3.isDeleted = 0 AND t3.parkingType = 'SELF'
                    AND t3.dropOffDateTime BETWEEN NOW() - INTERVAL 2 HOUR AND @dt1 + INTERVAL 33 - 2 DAY) s1
                INNER JOIN
            (SELECT
                @dt1 + INTERVAL D + 2 - 2 DAY dt_1,
                @dt2 + INTERVAL D + 2 - 2 DAY dt_2,
                    @q:=CAST(CURDATE() + INTERVAL (D * 24) + 12 HOUR AS DATETIME) q,
                    CAST(@dt1 + INTERVAL (D * 24) + 12 HOUR AS DATETIME) q0,
                    CAST(@dt2 + INTERVAL (D * 24) + 12 HOUR AS DATETIME) q1
            FROM
                (SELECT
                A + B D
            FROM
                (SELECT - 2 A UNION ALL SELECT - 1 UNION ALL SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) A
            CROSS JOIN (SELECT 0 B UNION ALL SELECT 8 UNION ALL SELECT 16 UNION ALL SELECT 24) B) s0) s2 ON s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                OR s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
        WHERE
            s2.dt_1 IS NOT NULL
        GROUP BY s2.dt_1;
        ;


        --  Set @wk0 :=0;
        --  Set @wk1:=2;
        --  SELECT
        --      @dt1:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 0 DAY f_0,
        --      @dt2:=CURDATE() - INTERVAL 1 * 7 * @wk1 + 0 DAY f_1
    ";

    const L1_pat_tpr = "SELECT
        @min_id:=0,
        @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 33 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d + 2 + 0 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d - 33 + 0 DAY t_1,
        @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
        @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 + @d DAY r_1";

    const L1_pat_apr = "SELECT
        @min_id:=0,
        @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 33 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d + 2 + 0 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d - 33 + 0 DAY t_1,
        @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
        @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 + @d DAY r_1";

    const L1_pat_cap = "SELECT
        @min_id:=0,
        @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 33 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d + 2 + 0 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d - 33 + 0 DAY t_1,
        @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
        @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 + @d DAY r_1";

    const L_pat_dropoff_lot1 = "SELECT
        @min_id:=0,
        @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 33 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d + 2 + 0 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d - 33 + 0 DAY t_1,
        @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
        @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 + @d DAY r_1";

    const L_pat_pickup_lot1 = "SELECT
        @min_id:=0,
        @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 33 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d + 2 + 0 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d - 33 + 0 DAY t_1,
        @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
        @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 + @d DAY r_1";
}
