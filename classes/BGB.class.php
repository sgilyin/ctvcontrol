<?php

/*
 * Copyright (C) 2020 Sergey Ilyin <developer@ilyins.ru>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class for BGB
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class BGB {
    public static function getStreets() {
        $query = "SELECT id, title FROM address_street";

        return static::executeQuery($query);
    }

    public static function getData($param) {
        $query = "SELECT CONCAT(tbl_street.title, ' д. ', tbl_house.house, CONCAT_WS( ' кв. ',tbl_house.frac, IF(tbl_flat.flat='',NULL,tbl_flat.flat))) AS 'address', tbl_phone.val AS 'phone', tbl_fio.val AS 'fio', tbl_balance.summa1+tbl_balance.summa2-tbl_balance.summa3-tbl_balance.summa4 AS 'balance', tbl_tariff_plan.title AS 'tariff', tbl_comment.val AS 'comment'
FROM contract tbl_contract
LEFT JOIN contract_parameter_type_1 tbl_phone ON (tbl_contract.id=tbl_phone.cid) AND (tbl_phone.pid=2)
LEFT JOIN contract_parameter_type_1 tbl_fio ON (tbl_contract.id=tbl_fio.cid) AND (tbl_fio.pid=1)
LEFT JOIN contract_parameter_type_1 tbl_comment ON (tbl_contract.id=tbl_comment.cid) AND (tbl_comment.pid=32)
LEFT JOIN contract_parameter_type_2 tbl_flat ON (tbl_contract.id=tbl_flat.cid)
LEFT JOIN address_house tbl_house ON (tbl_flat.hid=tbl_house.id)
LEFT JOIN address_street tbl_street ON (tbl_house.streetid=tbl_street.id)
LEFT JOIN contract_balance tbl_balance ON (tbl_balance.cid=tbl_contract.id AND (tbl_balance.mm=MONTH(CURDATE()) AND (tbl_balance.yy=YEAR(CURDATE()))))
LEFT JOIN contract_tariff tbl_tariff ON (tbl_tariff.cid=tbl_contract.id AND tbl_tariff.date2 IS NULL)
LEFT JOIN tariff_plan tbl_tariff_plan ON (tbl_tariff_plan.id=tbl_tariff.tpid)
WHERE tbl_contract.date2 IS NULL AND tbl_contract.fc=0 AND NOT (tbl_contract.gr&(1<<3) > 0) AND ";
        if ($param->textDebt){
            $query = $query."(tbl_balance.summa1+tbl_balance.summa2-tbl_balance.summa3-tbl_balance.summa4<-$param->textDebt OR tbl_balance.summa1+tbl_balance.summa2-tbl_balance.summa3-tbl_balance.summa4 IS NULL) AND ";
        }
        $query = $query."(tbl_street.id=$param->street_id AND CONCAT(tbl_house.house, '', tbl_house.frac)='$param->textHouse')
ORDER BY balance, address
LIMIT 1000";

        return static::executeQuery($query);
    }

    private static function executeQuery($query) {
        $mysqli = new mysqli(BGB_HOST, BGB_USER, BGB_PASS, BGB_DB);
        if (mysqli_connect_errno()) {
            printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
            exit;
        }
        $mysqli->query("set character_set_client='utf8'");
        $mysqli->query("set character_set_results='utf8'");
        $mysqli->query("set collation_connection='utf8_general_ci'");
        $bgb_result = $mysqli->query($query);

        return $bgb_result;
    }
}
