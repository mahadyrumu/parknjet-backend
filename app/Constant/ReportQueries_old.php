<?php

class ReportQueries_old
{

    const q_lot_use_40 = "SELECT
        @min_id:=0,
        @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 33 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d + 2 + 0 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + @d - 33 + 0 DAY t_1,
        @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
        @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 + @d DAY r_1";

    //- interval 8 hour
    // Total vehicle count pattern
    const q_lot_use_41 = "SELECT
        DATE_FORMAT(s2.dt, '%m-%d') h1,
            LEFT(DATE_FORMAT(s2.dt, '%a'), 2) h2,
            SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_0')  '0',
            SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt )  '0a',
            SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_1')  '1',
            SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt )  '1a'
    FROM
        (SELECT
            t1.do_dt, t1.pu_dt, t1.r_dt, t1.r_info_id
            FROM
                parknjet_db1.reservations t1
            WHERE
                ((t1.do_dt <= '@t_0' AND t1.pu_dt >= '@f_0')
                    OR (t1.do_dt <= '@t_1' AND t1.pu_dt >= '@f_1'))

            UNION SELECT
                dropOffDateTime AS do_dt,
                pickupDateTime pu_dt,
                t3.bookedDateTime r_dt,
                t3.id
            FROM
                reservation_info t3
                    LEFT JOIN
                customer_activity t4 ON t3.id = t4.reservation_id
            WHERE
                (t4.claimId IS NULL OR t4.claimId = 0)
                    AND t3.isDeleted = 0
                    AND t3.dropOffDateTime BETWEEN NOW() - INTERVAL 2 HOUR AND '@t_0'
            ) s1
                INNER JOIN
            (SELECT
                CURDATE() - INTERVAL 1 * 7 * @wk0 DAY + INTERVAL d DAY dt,
                    @q:=CAST(CURDATE() + INTERVAL (d * 24) + 12 HOUR AS DATETIME) q,
                    @q - INTERVAL 1 * 7 * @wk0 DAY q0,
                    @q - INTERVAL 1 * 7 * @wk1 + @d DAY q1
            FROM
                (
                    Select A + B D From
                    (
                    SELECT -2 A
                        UNION ALL SELECT -1
                        UNION ALL SELECT 0
                        UNION ALL SELECT 1
                        UNION ALL SELECT 2
                        UNION ALL SELECT 3
                        UNION ALL SELECT 4
                        UNION ALL SELECT 5
                    ) A
                    CROSS JOIN (SELECT 0 B
                        UNION ALL SELECT 8
                        UNION ALL SELECT 16
                        UNION ALL SELECT 24
                    ) B
                ) s0) s2
                ON
                s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                OR s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
            WHERE
            s2.dt IS NOT NULL
            GROUP BY s2.dt;";

    const q_lot_use_50 = self::q_lot_use_40;

    // self vehicle count pattern
    const q_lot_use_51 = "SELECT
    DATE_FORMAT(s2.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s2.dt, '%a'), 2) h2,
        SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_0')  '0',
        SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt )  '0a',
        SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_1')  '1',
        SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt )  '1a'
    FROM
        (SELECT
            t1.do_dt, t1.pu_dt, t1.r_dt, t1.r_info_id
            FROM
                parknjet_db1.reservations t1
            WHERE
                ((t1.do_dt <= '@t_0' AND t1.pu_dt >= '@f_0')
                    OR (t1.do_dt <= '@t_1' AND t1.pu_dt >= '@f_1'))
                    AND t1.parkingType in ('SELF')

            UNION SELECT
                dropOffDateTime AS do_dt,
                pickupDateTime pu_dt,
                t3.bookedDateTime r_dt,
                t3.id
            FROM
                reservation_info t3
                    LEFT JOIN
                customer_activity t4 ON t3.id = t4.reservation_id
            WHERE
                (t4.claimId IS NULL OR t4.claimId = 0) AND t3.parkingType in ('SELF')
                    AND t3.isDeleted = 0
                    AND t3.dropOffDateTime BETWEEN NOW() - INTERVAL 2 HOUR AND '@t_0'
            ) s1
                INNER JOIN
            (SELECT
                CURDATE() - INTERVAL 1 * 7 * @wk0 DAY + INTERVAL d DAY dt,
                    @q:=CAST(CURDATE() + INTERVAL (d * 24) + 12 HOUR AS DATETIME) q,
                    @q - INTERVAL 1 * 7 * @wk0 DAY q0,
                    @q - INTERVAL 1 * 7 * @wk1  + @d DAY q1
            FROM
                (
                SELECT -2 D UNION ALL SELECT -1 UNION ALL SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
                UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
                UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13
                UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18
                UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21) s0) s2
                ON
                s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
                OR s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
            WHERE
            s2.dt IS NOT NULL
            GROUP BY s2.dt;";

    // reservations
    const q_00 = "SELECT
    @tm:=time(now() ) tm,
    @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 8 DAY f_0,
    @t_0:=  NOW()   - INTERVAL 1 * 7 * @wk0 - 1 DAY t_0,
    @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + 8 DAY f_1,
    @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 - 1 DAY t_1,
    @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
    @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 DAY r_1";

    // - interval 8 hour
    const q_rsvn_10 = self::q_00;

    // rsvn all
    const q_rsvn_11 = "SELECT
        DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%a'), 23) h2,
        s0.w0 '0', s1.w1 '1',
        s0.w0a '0a', s1.w1a '1a'
    FROM
        (SELECT
            DATE(t0.bookedDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.bookedDateTime) <= '@tm') w0
            FROM
                reservation_info t0
            WHERE
                t0.isDeleted = 0 AND t0.id >= @min_id
                    AND (t0.bookedDateTime BETWEEN '@f_0' AND '@t_0')
            GROUP BY dt) s0
                LEFT JOIN
            (SELECT
                DATE(t1.bookedDateTime) dt,
                COUNT(id) w1a,
                SUM(time(t1.bookedDateTime) <= '@tm') w1

            FROM
                reservation_info t1

            WHERE
                t1.isDeleted = 0 AND t1.id >= @min_id
                    AND (t1.bookedDateTime BETWEEN '@f_1' AND '@t_1')
            GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * (@wk1-@wk0) DAY
            ORDER BY s0.dt DESC;";


    const q_rsvn_self_10 = self::q_00;

    // rsvn all
    const q_rsvn_self_11 = "SELECT
    DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%a'), 23) h2,
        s0.w0 '0', s1.w1 '1',
        s0.w0a '0a', s1.w1a '1a'
    FROM
        (SELECT
            DATE(t0.bookedDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.bookedDateTime) <= '@tm') w0
            FROM
                reservation_info t0
            WHERE
                t0.parkingType = 'SELF' AND
                t0.isDeleted = 0 AND t0.id >= @min_id
                    AND (t0.bookedDateTime BETWEEN '@f_0' AND '@t_0')
            GROUP BY dt) s0
                LEFT JOIN
            (SELECT
                DATE(t1.bookedDateTime) dt,
                COUNT(id) w1a,
                SUM(time(t1.bookedDateTime) <= '@tm') w1

            FROM
                reservation_info t1

            WHERE
                t1.parkingType = 'SELF' AND
                t1.isDeleted = 0 AND t1.id >= @min_id
                    AND (t1.bookedDateTime BETWEEN '@f_1' AND '@t_1')
            GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * (@wk1-@wk0) DAY
            ORDER BY s0.dt DESC;";


    // drop off
    const q_do_10 = "SELECT
    @min_id:=0 min_id,
    @tm:=time(now()) tm,
    @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 1 DAY f_0,
    @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 8 DAY t_0,
    @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + 1 DAY f_1,
    @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 - 8 DAY t_1,
    @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
    @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 DAY r_1";


    // drop off all
    const q_do_11 = "SELECT
        DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%a'), 23) h2,
        s0.w0 '0', s1.w1 '1',
        s0.w0a '0a', s1.w1a '1a'
    FROM
        (SELECT
            DATE(t0.dropOffDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.dropOffDateTime) <= '@tm') w0
        FROM
            reservation_info t0
        WHERE
            t0.isDeleted = 0 AND t0.id >= @min_id
                AND (t0.dropOffDateTime BETWEEN '@f_0' AND '@t_0')
        GROUP BY dt) s0
            LEFT JOIN
        (SELECT
            DATE(t1.dropOffDateTime) dt,
            COUNT(id) w1a,
            SUM(time(t1.dropOffDateTime) <= '@tm') w1

        FROM
            reservation_info t1

        WHERE
            t1.isDeleted = 0 AND t1.id >= @min_id
                AND (t1.dropOffDateTime BETWEEN '@f_1' AND '@t_1')
        GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * (@wk1-@wk0) DAY
        ORDER BY s0.dt DESC;";


    const q_do_self_10 = "SELECT
    @min_id:=0 min_id,
    @tm:=time(now()) tm,
    @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 1 DAY f_0,
    @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 8 DAY t_0,
    @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + 1 DAY f_1,
    @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 - 8 DAY t_1,
    @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
    @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 DAY r_1";


    // drop off self
    const q_do_self_11 = "SELECT
        DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%a'), 23) h2,
        s0.w0 '0', s1.w1 '1',
        s0.w0a '0a', s1.w1a '1a'
    FROM
        (SELECT
            DATE(t0.dropOffDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.dropOffDateTime) <= '@tm') w0
        FROM
            reservation_info t0
        WHERE
            t0.parkingType = 'SELF' AND
            t0.isDeleted = 0 AND t0.id >= @min_id
                AND (t0.dropOffDateTime BETWEEN '@f_0' AND '@t_0')
        GROUP BY dt) s0
            LEFT JOIN
        (SELECT
            DATE(t1.dropOffDateTime) dt,
            COUNT(id) w1a,
            SUM(time(t1.dropOffDateTime) <= '@tm') w1

        FROM
            reservation_info t1
        WHERE
            t1.parkingType = 'SELF' AND
            t1.isDeleted = 0 AND t1.id >= @min_id
                AND (t1.dropOffDateTime BETWEEN '@f_1' AND '@t_1')
        GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * (@wk1-@wk0) DAY
        ORDER BY s0.dt DESC;";


    const q_pu_self_10 = "SELECT
    @tm:=time(now()) tm,
    @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 2 DAY f_0,
    @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 40 DAY t_0,
    @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + 2 DAY f_1,
    @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 - 40 DAY t_1,
    @r_0:=NOW() - INTERVAL 1 * 7 * @wk0 DAY r_0,
    @r_1:=NOW() - INTERVAL 1 * 7 * @wk1 DAY r_1";


    // Total vehicle count pattern
    const q_pu_self_11 = "SELECT
        DATE_FORMAT(s2.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s2.dt, '%a'), 2) h2,
        SUM(s2.q0 = s1.pu_dt1 AND s1.r_dt <= '@r_0')  '0',
        SUM(s2.q0 = s1.pu_dt1 )  '0a',
        SUM(s2.q1 = s1.pu_dt1  AND s1.r_dt <= '@r_1')  '1',
        SUM(s2.q1 = s1.pu_dt1 )  '1a'
    FROM
        (SELECT
            date(t1.do_dt) do_dt1, date(t1.pu_dt) pu_dt1, t1.r_dt, t1.r_info_id
        FROM
            @db.reservations t1
        WHERE
            ((t1.do_dt <= '@t_0' AND t1.pu_dt >= '@f_0')
                OR (t1.do_dt <= '@t_1' AND t1.pu_dt >= '@f_1'))
            AND t1.parkingType in ('SELF')

        UNION SELECT
            date(dropOffDateTime) do_dt1,
            date(pickupDateTime) pu_dt1,
            t3.bookedDateTime r_dt,
            t3.id
        FROM
            reservation_info t3
                LEFT JOIN
            customer_activity t4 ON t3.id = t4.reservation_id
        WHERE
            (t4.claimId IS NULL OR t4.claimId = 0)  AND t3.parkingType in ('SELF')
                AND t3.isDeleted = 0
                AND t3.dropOffDateTime BETWEEN NOW() - INTERVAL 2 HOUR AND '@t_0'
        ) s1
            INNER JOIN
        (SELECT
            CURDATE() - INTERVAL 1 * 7 * @wk0 DAY + INTERVAL d DAY dt,
                @q:=CAST(CURDATE() + INTERVAL (d * 24) + 12 HOUR AS DATETIME) q,
                @q - INTERVAL 1 * 7 * @wk0 DAY q0,
                @q - INTERVAL 1 * 7 * @wk1 DAY q1
        FROM
            (
                Select A + B D From
                (
                SELECT -2 A
                    UNION ALL SELECT -1
                    UNION ALL SELECT 0
                    UNION ALL SELECT 1
                    UNION ALL SELECT 2
                    UNION ALL SELECT 3
                    UNION ALL SELECT 4
                    UNION ALL SELECT 5
                    UNION ALL SELECT 6
                    UNION ALL SELECT 7
                ) A
                CROSS JOIN (SELECT 0 B
                    UNION ALL SELECT 10
                    UNION ALL SELECT 20
                    UNION ALL SELECT 30
                ) B
            ) s0) s2
            ON
            s2.q0 BETWEEN s1.do_dt1 AND s1.pu_dt1
            OR s2.q1 BETWEEN s1.do_dt1 AND s1.pu_dt1
        WHERE
        s2.dt IS NOT NULL
        GROUP BY s2.dt;";


    const q_pu_10 = "SELECT
    @tm:=time(now()) tm,
    @f_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 + 1 DAY f_0,
    @t_0:=CURDATE() - INTERVAL 1 * 7 * @wk0 - 8 DAY t_0,
    @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 + 1 DAY f_1,
    @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk1 - 8 DAY t_1";

    // pick up self
    const q_pu_11 = "SELECT
        DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%a'), 2) h2,
        s0.w0 '0', s1.w1 '1',
        s0.w0a '0a', s1.w1a '1a'
    FROM
        (SELECT
            DATE(t0.pickUpDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.pickUpDateTime) <= '@tm') w0
        FROM
            reservation_info t0
        WHERE
            t0.isDeleted = 0
                AND (t0.pickUpDateTime BETWEEN '@f_0' AND '@t_0')
        GROUP BY dt) s0
            LEFT JOIN
        (SELECT
            DATE(t1.pickUpDateTime) dt,
            COUNT(id) w1a,
            SUM(time(t1.pickUpDateTime) <= '@tm') w1

        FROM
            reservation_info t1

        WHERE
            t1.isDeleted = 0
                AND (t1.pickUpDateTime BETWEEN '@f_1' AND '@t_1')
        GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * (@wk1-@wk0) DAY
        ORDER BY s0.dt DESC;";

    const q_rsvn_0 = "SELECT
        @min_id:=min(t1.id) min_id, @tm:=time(now()) tm,
        @f_0:=CURDATE() - INTERVAL + 7 DAY f_0,
        @t_0:=  NOW() t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk + 8 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 1 DAY t_1,
        @f_2:=CURDATE() - INTERVAL 2 * 7 * @wk + 8 DAY f_2,
        @t_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 1 DAY t_2,
        @f_3:=CURDATE() - INTERVAL 3 * 7 * @wk + 8 DAY f_3,
        @t_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 1 DAY t_3,
        @f_4:=CURDATE() - INTERVAL 4 * 7 * @wk + 8 DAY f_4,
        @t_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 1 DAY t_4
    FROM
        reservation_info t1
    WHERE
        t1.isDeleted = 0 AND t1.id >= 191320
            AND (t1.bookedDateTime >= CURDATE() - INTERVAL 4 * 7 * @wk + 8 DAY)
    ORDER BY id
    LIMIT 0 , 1;";

    const q_rsvn = "SELECT
        DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%W'), 3) h2,
        s0.w0 '0', s1.w1 '1', s2.w2 '2', s3.w3 '3', s4.w4 '4',
        s0.w0a '0a', s1.w1a '1a', s2.w2a '2a', s3.w3a '3a', s4.w4a '4a'
    FROM
        (SELECT
            DATE(t0.bookedDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.bookedDateTime) <= '@tm') w0
        FROM
            reservation_info t0
        WHERE
            t0.isDeleted = 0 AND t0.id >= @min_id
                AND (t0.bookedDateTime BETWEEN '@f_0' AND '@t_0')
        GROUP BY dt) s0
            LEFT JOIN
        (SELECT
            DATE(t1.bookedDateTime) dt,
            COUNT(id) w1a,
            SUM(time(t1.bookedDateTime) <= '@tm') w1

        FROM
            reservation_info t1

        WHERE
            t1.isDeleted = 0 AND t1.id >= @min_id
                AND (t1.bookedDateTime BETWEEN '@f_1' AND '@t_1')
        GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t2.bookedDateTime) dt,
            COUNT(id) w2a,
            SUM(time(t2.bookedDateTime) <= '@tm') w2
        FROM
            reservation_info t2
        WHERE
            t2.isDeleted = 0 AND t2.id >= @min_id
                AND (t2.bookedDateTime BETWEEN '@f_2' AND '@t_2')
        GROUP BY dt) s2 ON s0.dt = s2.dt + INTERVAL 2 * 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t3.bookedDateTime) dt,
            COUNT(id) w3a,
            SUM(time(t3.bookedDateTime) <= '@tm') w3

        FROM
            reservation_info t3
        WHERE
            t3.isDeleted = 0 AND t3.id >= @min_id
                AND (t3.bookedDateTime BETWEEN '@f_3' AND '@t_3')
        GROUP BY dt) s3 ON s0.dt = s3.dt + INTERVAL 3 * 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t4.bookedDateTime) dt,
            COUNT(id) w4a,
            SUM(time(t4.bookedDateTime) <= '@tm') w4
        FROM
            reservation_info t4
        WHERE
            t4.isDeleted = 0 AND t4.id >= @min_id
                AND (t4.bookedDateTime BETWEEN '@f_4' AND '@t_4')
        GROUP BY dt) s4 ON s0.dt = s4.dt + INTERVAL 4 * 7 * @wk DAY
        ORDER BY s0.dt DESC;";

    const q_do_0 = "SELECT
        @min_id:=min(t1.id) min_id, @tm:=time(now()) tm,
        @f_0:=CURDATE() - INTERVAL + 7 DAY f_0,
        @t_0:=  NOW() t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk + 8 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 1 DAY t_1,
        @f_2:=CURDATE() - INTERVAL 2 * 7 * @wk + 8 DAY f_2,
        @t_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 1 DAY t_2,
        @f_3:=CURDATE() - INTERVAL 3 * 7 * @wk + 8 DAY f_3,
        @t_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 1 DAY t_3,
        @f_4:=CURDATE() - INTERVAL 4 * 7 * @wk + 8 DAY f_4,
        @t_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 1 DAY t_4
    FROM
        reservation_info t1
    WHERE
        t1.isDeleted = 0 -- AND t1.id >= 191320
            AND (t1.dropOffDateTime >= CURDATE() - INTERVAL 4 * 7 * @wk + 8 DAY)
    ORDER BY id
    LIMIT 0 , 1;";


    const q_do = "SELECT
        DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%W'), 3) h2,
        s0.w0 '0', s1.w1 '1', s2.w2 '2', s3.w3 '3', s4.w4 '4',
        s0.w0a '0a', s1.w1a '1a', s2.w2a '2a', s3.w3a '3a', s4.w4a '4a'
    FROM
        (SELECT
            DATE(t0.dropOffDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.dropOffDateTime) <= '@tm') w0
        FROM
            reservation_info t0
        WHERE
            t0.isDeleted = 0  AND t0.id >= @min_id
                AND (t0.dropOffDateTime BETWEEN '@f_0' AND '@t_0')
        GROUP BY dt) s0
            LEFT JOIN
        (SELECT
            DATE(t1.dropOffDateTime) dt,
            COUNT(id) w1a,
            SUM(time(t1.dropOffDateTime) <= '@tm') w1

        FROM
            reservation_info t1

        WHERE
            t1.isDeleted = 0  AND t1.id >= @min_id
                AND (t1.dropOffDateTime BETWEEN '@f_1' AND '@t_1')
        GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t2.dropOffDateTime) dt,
            COUNT(id) w2a,
            SUM(time(t2.dropOffDateTime) <= '@tm') w2
        FROM
            reservation_info t2
        WHERE
            t2.isDeleted = 0  AND t2.id >= @min_id
                AND (t2.dropOffDateTime BETWEEN '@f_2' AND '@t_2')
        GROUP BY dt) s2 ON s0.dt = s2.dt + INTERVAL 2 * 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t3.dropOffDateTime) dt,
            COUNT(id) w3a,
            SUM(time(t3.dropOffDateTime) <= '@tm') w3

        FROM
            reservation_info t3
        WHERE
            t3.isDeleted = 0  AND t3.id >= @min_id
                AND (t3.dropOffDateTime BETWEEN '@f_3' AND '@t_3')
        GROUP BY dt) s3 ON s0.dt = s3.dt + INTERVAL 3 * 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t4.dropOffDateTime) dt,
            COUNT(id) w4a,
            SUM(time(t4.dropOffDateTime) <= '@tm') w4
        FROM
            reservation_info t4
        WHERE
            t4.isDeleted = 0  AND t4.id >= @min_id
                AND (t4.dropOffDateTime BETWEEN '@f_4' AND '@t_4')
        GROUP BY dt) s4 ON s0.dt = s4.dt + INTERVAL 4 * 7 * @wk DAY
        ORDER BY s0.dt DESC;";


    const q_do_s_0 = "SELECT
        @min_id:=min(t1.id) min_id, @tm:=time(now()) tm,
        @f_0:=CURDATE() - INTERVAL + 0 DAY f_0,
        @t_0:=CURDATE() - INTERVAL - 7 DAY  t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 0 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 7 DAY t_1,
        @f_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 0 DAY f_2,
        @t_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 7 DAY t_2,
        @f_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 0 DAY f_3,
        @t_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 7 DAY t_3,
        @f_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 0 DAY f_4,
        @t_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 7 DAY t_4
    FROM
        reservation_info t1
    WHERE
        t1.isDeleted = 0 -- AND t1.id >= 191320
            AND (t1.dropOffDateTime >= CURDATE() - INTERVAL 4 * 7 * @wk + 8 DAY)
    ORDER BY id
    LIMIT 0 , 1;";


    const q_do_s = "SELECT
        DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%W'), 3) h2,
        s0.w0 '0', s1.w1 '1', s2.w2 '2', s3.w3 '3', s4.w4 '4',
        s0.w0a '0a', s1.w1a '1a', s2.w2a '2a', s3.w3a '3a', s4.w4a '4a'
    FROM
        (SELECT
            DATE(t0.dropOffDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.dropOffDateTime) <= '@tm') w0
        FROM
            reservation_info t0
        WHERE
            t0.isDeleted = 0  AND t0.id >= @min_id AND t0.parkingType in ('SELF')
                AND (t0.dropOffDateTime BETWEEN '@f_0' AND '@t_0')
        GROUP BY dt) s0
            LEFT JOIN
        (SELECT
            DATE(t1.dropOffDateTime) dt,
            COUNT(id) w1a,
            SUM(time(t1.dropOffDateTime) <= '@tm') w1

        FROM
            reservation_info t1

        WHERE
            t1.isDeleted = 0  AND t1.id >= @min_id AND t1.parkingType in ('SELF')
                AND (t1.dropOffDateTime BETWEEN '@f_1' AND '@t_1')
        GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t2.dropOffDateTime) dt,
            COUNT(id) w2a,
            SUM(time(t2.dropOffDateTime) <= '@tm') w2
        FROM
            reservation_info t2
        WHERE
            t2.isDeleted = 0  AND t2.id >= @min_id AND t2.parkingType in ('SELF')
                AND (t2.dropOffDateTime BETWEEN '@f_2' AND '@t_2')
        GROUP BY dt) s2 ON s0.dt = s2.dt + INTERVAL 2 * 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t3.dropOffDateTime) dt,
            COUNT(id) w3a,
            SUM(time(t3.dropOffDateTime) <= '@tm') w3

        FROM
            reservation_info t3
        WHERE
            t3.isDeleted = 0  AND t3.id >= @min_id AND t3.parkingType in ('SELF')
                AND (t3.dropOffDateTime BETWEEN '@f_3' AND '@t_3')
        GROUP BY dt) s3 ON s0.dt = s3.dt + INTERVAL 3 * 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t4.dropOffDateTime) dt,
            COUNT(id) w4a,
            SUM(time(t4.dropOffDateTime) <= '@tm') w4
        FROM
            reservation_info t4
        WHERE
            t4.isDeleted = 0  AND t4.id >= @min_id AND t4.parkingType in ('SELF')
                AND (t4.dropOffDateTime BETWEEN '@f_4' AND '@t_4')
        GROUP BY dt) s4 ON s0.dt = s4.dt + INTERVAL 4 * 7 * @wk DAY
        ORDER BY s0.dt DESC;";


    const q_pu_s_0 = "SELECT
        @min_id:=min(t1.id) min_id, @tm:=time(now()) tm,
        @f_0:=CURDATE() - INTERVAL + 0 DAY f_0,
        @t_0:=CURDATE() - INTERVAL - 7 DAY  t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 0 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 7 DAY t_1,
        @f_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 0 DAY f_2,
        @t_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 7 DAY t_2,
        @f_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 0 DAY f_3,
        @t_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 7 DAY t_3,
        @f_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 0 DAY f_4,
        @t_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 7 DAY t_4
    FROM
        reservation_info t1
    WHERE
        t1.isDeleted = 0 -- AND t1.id >= 191320
            AND (t1.pickUpDateTime >= CURDATE() - INTERVAL 4 * 7 * @wk + 8 DAY)
    ORDER BY id
    LIMIT 0 , 1;";


    const q_pu_s = "SELECT
        DATE_FORMAT(s0.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s0.dt, '%W'), 3) h2,
        s0.w0 '0', s1.w1 '1', s2.w2 '2', s3.w3 '3', s4.w4 '4',
        s0.w0a '0a', s1.w1a '1a', s2.w2a '2a', s3.w3a '3a', s4.w4a '4a'
    FROM
        (SELECT
            DATE(t0.pickUpDateTime) dt,
            COUNT(id) w0a,
            SUM(time(t0.pickUpDateTime) <= '@tm') w0
        FROM
            reservation_info t0
        WHERE
            t0.isDeleted = 0  AND t0.id >= @min_id AND t0.parkingType in ('SELF')
                AND (t0.pickUpDateTime BETWEEN '@f_0' AND '@t_0')
        GROUP BY dt) s0
            LEFT JOIN
        (SELECT
            DATE(t1.pickUpDateTime) dt,
            COUNT(id) w1a,
            SUM(time(t1.pickUpDateTime) <= '@tm') w1

        FROM
            reservation_info t1

        WHERE
            t1.isDeleted = 0  AND t1.id >= @min_id AND t1.parkingType in ('SELF')
                AND (t1.pickUpDateTime BETWEEN '@f_1' AND '@t_1')
        GROUP BY dt) s1 ON s0.dt = s1.dt + INTERVAL 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t2.pickUpDateTime) dt,
            COUNT(id) w2a,
            SUM(time(t2.pickUpDateTime) <= '@tm') w2
        FROM
            reservation_info t2
        WHERE
            t2.isDeleted = 0  AND t2.id >= @min_id AND t2.parkingType in ('SELF')
                AND (t2.pickUpDateTime BETWEEN '@f_2' AND '@t_2')
        GROUP BY dt) s2 ON s0.dt = s2.dt + INTERVAL 2 * 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t3.pickUpDateTime) dt,
            COUNT(id) w3a,
            SUM(time(t3.pickUpDateTime) <= '@tm') w3

        FROM
            reservation_info t3
        WHERE
            t3.isDeleted = 0  AND t3.id >= @min_id AND t3.parkingType in ('SELF')
                AND (t3.pickUpDateTime BETWEEN '@f_3' AND '@t_3')
        GROUP BY dt) s3 ON s0.dt = s3.dt + INTERVAL 3 * 7 * @wk DAY
            LEFT JOIN
        (SELECT
            DATE(t4.pickUpDateTime) dt,
            COUNT(id) w4a,
            SUM(time(t4.pickUpDateTime) <= '@tm') w4
        FROM
            reservation_info t4
        WHERE
            t4.isDeleted = 0  AND t4.id >= @min_id AND t4.parkingType in ('SELF')
                AND (t4.pickUpDateTime BETWEEN '@f_4' AND '@t_4')
        GROUP BY dt) s4 ON s0.dt = s4.dt + INTERVAL 4 * 7 * @wk DAY
        ORDER BY s0.dt DESC;";


    const q_tpr_w_pat_0 = "SELECT @wkc := WEEK(curdate(),1) wkc,
        @r_0:=now() r_0,
        @r_1:=now() - interval 1 week r_1,
        @r_2:=now() - interval 2 week r_2,
        @r_3:=now() - interval 3 week r_3,
        @r_4:=now() - interval 4 week r_4";


    const q_tpr_w_pat = "SELECT
        t2.code h1,
        t2.sort h2,
        SUM(@wkc - WEEK(rsvn_date,1) = 0 and rsvn_date <= '@r_0') '0',
        SUM(@wkc - WEEK(rsvn_date,1) = 0) '0a',
        SUM(@wkc - WEEK(rsvn_date,1) = 1 and rsvn_date <= '@r_1') '1',
        SUM(@wkc - WEEK(rsvn_date,1) = 1 ) '1a',
        SUM(@wkc - WEEK(rsvn_date,1) = 2 and rsvn_date <= '@r_2') '2',
        SUM(@wkc - WEEK(rsvn_date,1) = 2 ) '2a',
        SUM(@wkc - WEEK(rsvn_date,1) = 3 and rsvn_date <= '@r_3') '3',
        SUM(@wkc - WEEK(rsvn_date,1) = 3 ) '3a',
        SUM(@wkc - WEEK(rsvn_date,1) = 4 and rsvn_date <= '@r_4') '4',
        SUM(@wkc - WEEK(rsvn_date,1) = 4 ) '4a'
    FROM
        parknjet_db1.tpr t1
            LEFT JOIN
        parknjet_db1.vendors t2 ON t1.vendor_id = t2.id
    WHERE
        x = 0
            AND rsvn_date >= CURDATE() - INTERVAL 5 WEEK
    GROUP BY vendor_id
    ORDER BY t2.sort";


    const q_res_w_pat_0 = "SELECT @wkc := WEEK(curdate(),1) wkc,
        @r_0:=time(now()) r_0,
        @r_1:=now() - interval 1 week r_1,
        @r_2:=now() - interval 2 week r_2,
        @r_3:=now() - interval 3 week r_3,
        @r_4:=now() - interval 4 week r_4";


    const q_res_w_pat = "SELECT
        sourceRSVN h1,
        '' h2,
        SUM(@wkc - WEEK(bookedDateTime,1) = 0 and time(bookedDateTime) <= '@r_0') '0',
        SUM(@wkc - WEEK(bookedDateTime,1) = 0) '0a',
        SUM(@wkc - WEEK(bookedDateTime,1) = 1 and time(bookedDateTime) <= '@r_0') '1',
        SUM(@wkc - WEEK(bookedDateTime,1) = 1) '1a',
        SUM(@wkc - WEEK(bookedDateTime,1) = 2 and time(bookedDateTime) <= '@r_0') '2',
        SUM(@wkc - WEEK(bookedDateTime,1) = 2) '2a',
        SUM(@wkc - WEEK(bookedDateTime,1) = 3 and time(bookedDateTime) <= '@r_0') '3',
        SUM(@wkc - WEEK(bookedDateTime,1) = 3) '3a',
        SUM(@wkc - WEEK(bookedDateTime,1) = 4 and time(bookedDateTime) <= '@r_0') '4',
        SUM(@wkc - WEEK(bookedDateTime,1) = 4) '4a'
    FROM
        reservation_info t1
    LEFT JOIN
        parknjet_db1.vendors t2 ON t1.sourceRSVN = t2.code
    WHERE
        isDeleted = 0
            AND bookedDateTime >= CURDATE() - INTERVAL 5 WEEK
    GROUP BY sourceRSVN
    ORDER BY t2.sort";

    // older queries
    const q_lot_use_10 = "SELECT
        @min_id := t1.id min_id,
        @f_0:=CURDATE() - INTERVAL + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL - 8 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk + 2 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 8 DAY t_1,
        @f_2:=CURDATE() - INTERVAL 2 * 7 * @wk + 2 DAY f_2,
        @t_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 8 DAY t_2,
        @f_3:=CURDATE() - INTERVAL 3 * 7 * @wk + 2 DAY f_3,
        @t_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 8 DAY t_3,
        @f_4:=CURDATE() - INTERVAL 4 * 7 * @wk + 2 DAY f_4,
        @t_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 8 DAY t_4
    FROM
        customer_activity t1
            INNER JOIN
        pickup_info t2 ON t1.pickupDetails_id = t2.id
    WHERE
        t1.id > 66000 AND t1.claimid > 0
            AND t2.actualPickUpDateTime >= CURDATE() - INTERVAL 1 + 4 * 7 * @wk DAY
    ORDER BY t1.id
    LIMIT 0 , 1;";


    const q_lot_use_11 = "SELECT
        DATE_FORMAT(s2.dt, '%m-%d') date,
        LEFT(DATE_FORMAT(s2.dt, '%W'), 3) day,
        SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt)  '0',
        SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt)  '1',
        SUM(s2.q2 BETWEEN s1.do_dt AND s1.pu_dt)  '2',
        SUM(s2.q3 BETWEEN s1.do_dt AND s1.pu_dt)  '3',
        SUM(s2.q4 BETWEEN s1.do_dt AND s1.pu_dt)  '4'
    FROM
        (SELECT
            t2.actualDropOffDateTime do_dt,
                t3.actualPickupDateTime pu_dt, t4.id
        FROM
            customer_activity t1
        LEFT JOIN dropoff_info t2 ON t1.dropOffDetails_id = t2.id
        LEFT JOIN pickup_info t3 ON t1.pickupDetails_id = t3.id
        LEFT JOIN reservation_info t4 ON t1.reservation_id = t4.id
        WHERE
            (t1.id in(54460) or t1.id >= 118570) AND t1.claimid > 0
                AND (t1.parkingStatus NOT IN ('OUT')
                OR (t2.actualDropOffDateTime <= '@t_0'
                AND t3.actualPickupDateTime >=  '@f_0' )
                OR (t2.actualDropOffDateTime <= '@t_1'
                AND t3.actualPickupDateTime >=  '@f_1' )
                OR (t2.actualDropOffDateTime <= '@t_2'
                AND t3.actualPickupDateTime >=  '@f_2' )
                OR (t2.actualDropOffDateTime <= '@t_3'
                AND t3.actualPickupDateTime >=  '@f_3' )
                OR (t2.actualDropOffDateTime <= '@t_4'
                AND t3.actualPickupDateTime >=  '@f_4'))
                UNION SELECT
                dropOffDateTime AS do_dt, pickupDateTime pu_dt, t3.id
        FROM
            reservation_info t3
        LEFT JOIN customer_activity t4 ON t3.id = t4.reservation_id
        WHERE
            (t4.claimId IS NULL OR t4.claimId = 0)
                AND t3.isDeleted = 0
                AND t3.dropOffDateTime BETWEEN NOW() - INTERVAL 2 HOUR AND '@t_0'
                ) s1
            INNER JOIN
        (SELECT
            CURDATE() + INTERVAL d DAY dt,
                @q0:=CAST(CURDATE() + INTERVAL (d * 24) + 12 HOUR AS DATETIME) q0,
                @q0 - INTERVAL 1 * 7 * @wk DAY q1,
                @q0 - INTERVAL 2 * 7 * @wk DAY q2,
                @q0 - INTERVAL 3 * 7 * @wk DAY q3,
                @q0 - INTERVAL 4 * 7 * @wk DAY q4
        FROM
            (
            SELECT -2 D UNION ALL SELECT -1 UNION ALL SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7) s0) s2
            ON
            s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q2 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q3 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q4 BETWEEN s1.do_dt AND s1.pu_dt
        WHERE
        s2.dt IS NOT NULL
        GROUP BY s2.dt;";


    const q_lot_use_20 = "SELECT
        @min_id := min(t1.id) min_id ,
        @f_0:=CURDATE() - INTERVAL + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL - 12 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk + 2 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 12 DAY t_1,
        @f_2:=CURDATE() - INTERVAL 2 * 7 * @wk + 2 DAY f_2,
        @t_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 12 DAY t_2,
        @f_3:=CURDATE() - INTERVAL 3 * 7 * @wk + 2 DAY f_3,
        @t_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 12 DAY t_3,
        @f_4:=CURDATE() - INTERVAL 4 * 7 * @wk + 2 DAY f_4,
        @t_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 12 DAY t_4,
        @r_0:= NOW() r_0,
        @r_1:= NOW() - INTERVAL 1 * 7 * @wk DAY r_1,
        @r_2:= NOW() - INTERVAL 2 * 7 * @wk DAY r_2,
        @r_3:= NOW() - INTERVAL 3 * 7 * @wk DAY r_3,
        @r_4:= NOW() - INTERVAL 4 * 7 * @wk DAY r_4
    FROM
        customer_activity t1
            INNER JOIN
        pickup_info t2 ON t1.pickupDetails_id = t2.id
    WHERE
        t1.id > 0 AND t1.claimid > 0
            AND t2.actualPickUpDateTime >= CURDATE() - INTERVAL  1+ 4 * 7 * @wk  DAY
    ORDER BY t1.id
    LIMIT 0 , 1;";

    // older total vehicle count pattern
    const q_lot_use_21 = "SELECT
        DATE_FORMAT(s2.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s2.dt, '%W'), 3) h2,
        SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_0')  '0',
        SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt )  '0a',
        SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_1')  '1',
        SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt )  '1a',
        SUM(s2.q2 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_2')  '2',
        SUM(s2.q2 BETWEEN s1.do_dt AND s1.pu_dt )  '2a',
        SUM(s2.q3 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_3')  '3',
        SUM(s2.q3 BETWEEN s1.do_dt AND s1.pu_dt )  '3a',
        SUM(s2.q4 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_4')  '4',
        SUM(s2.q4 BETWEEN s1.do_dt AND s1.pu_dt )  '4a'

    FROM
        (SELECT

            t2.actualDropOffDateTime do_dt,
                t3.actualPickupDateTime pu_dt,
                t4.bookedDateTime r_dt,
                t4.id
        FROM
            customer_activity t1
        LEFT JOIN dropoff_info t2 ON t1.dropOffDetails_id = t2.id
        LEFT JOIN pickup_info t3 ON t1.pickupDetails_id = t3.id
        LEFT JOIN reservation_info t4 ON t1.reservation_id = t4.id
        WHERE
            (t1.id in(54460) or t1.id >= @min_id) AND t1.claimid > 0
                AND (t1.parkingStatus NOT IN ('OUT')
                OR (t2.actualDropOffDateTime <= '@t_0'
                AND t3.actualPickupDateTime >=  '@f_0' )
                OR (t2.actualDropOffDateTime <= '@t_1'
                AND t3.actualPickupDateTime >=  '@f_1' )
                OR (t2.actualDropOffDateTime <= '@t_2'
                AND t3.actualPickupDateTime >=  '@f_2' )
                OR (t2.actualDropOffDateTime <= '@t_3'
                AND t3.actualPickupDateTime >=  '@f_3' )
                OR (t2.actualDropOffDateTime <= '@t_4'
                AND t3.actualPickupDateTime >=  '@f_4' )
                )
                UNION SELECT
                dropOffDateTime AS do_dt, pickupDateTime pu_dt,
                t3.bookedDateTime r_dt,
                t3.id
        FROM
            reservation_info t3
        LEFT JOIN customer_activity t4 ON t3.id = t4.reservation_id
        WHERE
            (t4.claimId IS NULL OR t4.claimId = 0)
                AND t3.isDeleted = 0
                AND t3.dropOffDateTime BETWEEN NOW() - INTERVAL 2 HOUR AND '@t_0'
                ) s1
            INNER JOIN
        (SELECT
            CURDATE() + INTERVAL d DAY dt,
                @q0:=CAST(CURDATE() + INTERVAL (d * 24) + 12 HOUR AS DATETIME) q0,
                @q0 - INTERVAL 1 * 7 * @wk DAY q1,
                @q0 - INTERVAL 2 * 7 * @wk DAY q2,
                @q0 - INTERVAL 3 * 7 * @wk DAY q3,
                @q0 - INTERVAL 4 * 7 * @wk DAY q4
        FROM
            (
            SELECT -2 D UNION ALL SELECT -1 UNION ALL SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
            UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
            UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) s0) s2
            ON
            s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q2 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q3 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q4 BETWEEN s1.do_dt AND s1.pu_dt
    WHERE
        s2.dt IS NOT NULL
    GROUP BY s2.dt";


    const q_lot_use_30 = "SELECT
        @min_id := min(t1.id) min_id ,
        @f_0:=CURDATE() - INTERVAL + 2 DAY f_0,
        @t_0:=CURDATE() - INTERVAL - 12 DAY t_0,
        @f_1:=CURDATE() - INTERVAL 1 * 7 * @wk + 2 DAY f_1,
        @t_1:=CURDATE() - INTERVAL 1 * 7 * @wk - 12 DAY t_1,
        @f_2:=CURDATE() - INTERVAL 2 * 7 * @wk + 2 DAY f_2,
        @t_2:=CURDATE() - INTERVAL 2 * 7 * @wk - 12 DAY t_2,
        @f_3:=CURDATE() - INTERVAL 3 * 7 * @wk + 2 DAY f_3,
        @t_3:=CURDATE() - INTERVAL 3 * 7 * @wk - 12 DAY t_3,
        @f_4:=CURDATE() - INTERVAL 4 * 7 * @wk + 2 DAY f_4,
        @t_4:=CURDATE() - INTERVAL 4 * 7 * @wk - 12 DAY t_4,
        @r_0:= NOW() r_0,
        @r_1:= NOW() - INTERVAL 1 * 7 * @wk DAY r_1,
        @r_2:= NOW() - INTERVAL 2 * 7 * @wk DAY r_2,
        @r_3:= NOW() - INTERVAL 3 * 7 * @wk DAY r_3,
        @r_4:= NOW() - INTERVAL 4 * 7 * @wk DAY r_4
    FROM
        customer_activity t1
            INNER JOIN
        pickup_info t2 ON t1.pickupDetails_id = t2.id
    WHERE
        t1.id > 0 AND t1.claimid > 0
            AND t2.actualPickUpDateTime >= CURDATE() - INTERVAL  1+ 4 * 7 * @wk  DAY
    ORDER BY t1.id
    LIMIT 0 , 1;";



    // older self vehicle count pattern
    const q_lot_use_31 = "SELECT
        DATE_FORMAT(s2.dt, '%m-%d') h1,
        LEFT(DATE_FORMAT(s2.dt, '%W'), 3) h2,
        SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_0')  '0',
        SUM(s2.q0 BETWEEN s1.do_dt AND s1.pu_dt )  '0a',
        SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_1')  '1',
        SUM(s2.q1 BETWEEN s1.do_dt AND s1.pu_dt )  '1a',
        SUM(s2.q2 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_2')  '2',
        SUM(s2.q2 BETWEEN s1.do_dt AND s1.pu_dt )  '2a',
        SUM(s2.q3 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_3')  '3',
        SUM(s2.q3 BETWEEN s1.do_dt AND s1.pu_dt )  '3a',
        SUM(s2.q4 BETWEEN s1.do_dt AND s1.pu_dt AND s1.r_dt <= '@r_4')  '4',
        SUM(s2.q4 BETWEEN s1.do_dt AND s1.pu_dt )  '4a'

    FROM
        (SELECT
            t2.actualDropOffDateTime do_dt,
                t3.actualPickupDateTime pu_dt,
                t4.bookedDateTime r_dt,
                t4.id
        FROM
            customer_activity t1
        LEFT JOIN dropoff_info t2 ON t1.dropOffDetails_id = t2.id
        LEFT JOIN pickup_info t3 ON t1.pickupDetails_id = t3.id
        LEFT JOIN reservation_info t4 ON t1.reservation_id = t4.id
        WHERE
            (t1.id in(54460) or t1.id >= @min_id) AND t1.claimid > 0  AND t4.parkingType in ('SELF')
                AND (t1.parkingStatus NOT IN ('OUT')
                OR (t2.actualDropOffDateTime <= '@t_0'
                AND t3.actualPickupDateTime >=  '@f_0' )
                OR (t2.actualDropOffDateTime <= '@t_1'
                AND t3.actualPickupDateTime >=  '@f_1' )
                OR (t2.actualDropOffDateTime <= '@t_2'
                AND t3.actualPickupDateTime >=  '@f_2' )
                OR (t2.actualDropOffDateTime <= '@t_3'
                AND t3.actualPickupDateTime >=  '@f_3' )
                OR (t2.actualDropOffDateTime <= '@t_4'
                AND t3.actualPickupDateTime >=  '@f_4' )
                )
                UNION SELECT
                dropOffDateTime AS do_dt, pickupDateTime pu_dt,
                t3.bookedDateTime r_dt,
                t3.id
        FROM
            reservation_info t3
        LEFT JOIN customer_activity t4 ON t3.id = t4.reservation_id
        WHERE
            (t4.claimId IS NULL OR t4.claimId = 0) AND t3.parkingType in ('SELF')
                AND t3.isDeleted = 0
                AND t3.dropOffDateTime BETWEEN NOW() - INTERVAL 2 HOUR AND '@t_0'
                ) s1
            INNER JOIN
        (SELECT
            CURDATE() + INTERVAL d DAY dt,
                @q0:=CAST(CURDATE() + INTERVAL (d * 24) + 12 HOUR AS DATETIME) q0,
                @q0 - INTERVAL 1 * 7 * @wk DAY q1,
                @q0 - INTERVAL 2 * 7 * @wk DAY q2,
                @q0 - INTERVAL 3 * 7 * @wk DAY q3,
                @q0 - INTERVAL 4 * 7 * @wk DAY q4
        FROM
            (
            SELECT -2 D UNION ALL SELECT -1 UNION ALL SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
            UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
            UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) s0) s2
            ON
            s2.q0 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q1 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q2 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q3 BETWEEN s1.do_dt AND s1.pu_dt
            OR s2.q4 BETWEEN s1.do_dt AND s1.pu_dt
    WHERE
        s2.dt IS NOT NULL
    GROUP BY s2.dt";
}
