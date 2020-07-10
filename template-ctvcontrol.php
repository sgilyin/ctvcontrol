<?php

/*
Template Name: CTVControl
Template Post Type: page
*/

/**
 * @author Sergey Ilyin <developer@ilyins.ru>
 */

include_once 'config.php';

spl_autoload_register(function ($class) {
    switch ($class) {
        case 'Woocommerce':
            break;

        default:
            include __DIR__."/classes/$class.class.php";
            break;
    }
});

if ($_POST){
    $param->textDebt = $_POST['textDebt'] ?? false;
    $param->street_id = $_POST['street_id'] ?? false;
    $param->textHouse = $_POST['textHouse'] ?? false;
    $bgbData = BGB::getData($param);
    header( "Content-Type: application/vnd.ms-excel" );
    header( "Content-disposition: attachment; filename=".date('Y-m-d')."-$param->street_id-$param->textHouse-$param->textDebt.xls" );
    echo iconv('UTF-8', 'windows-1251', 'Адрес') . "\t" .
        iconv('UTF-8', 'windows-1251', 'Телефон') . "\t" .
        iconv('UTF-8', 'windows-1251', 'ФИО') . "\t" .
        iconv('UTF-8', 'windows-1251', 'Баланс') . "\t" .
        iconv('UTF-8', 'windows-1251', 'Тариф') . "\t" .
        iconv('UTF-8', 'windows-1251', 'Комментарий') . "\n";
        for ($i =0; $i < $bgbData->num_rows; $i++){
            $row = $bgbData->fetch_object();
            echo iconv('UTF-8', 'windows-1251', $row->address) . "\t" .
                $row->phone . "\t" .
                iconv('UTF-8', 'windows-1251', $row->fio) . "\t" .
                $row->balance . "\t" .
                iconv('UTF-8', 'windows-1251', $row->tariff) . "\t" .
                iconv('UTF-8', 'windows-1251', $row->comment) . "\n";
        }
} else {
    get_header();
    $streets = BGB::getStreets();
    echo '<div align="center"><form method="post">'
        . 'Должники по дому с суммой долга более '
        . '<input type="text" name="textDebt" id="textDebt">'
        . ' рублей<br>'
        . '<select name="street_id">'
        . '<option value="0">Выберите улицу</option>';

    for ($i =1; $i < $streets->num_rows; $i++){
        $street = $streets->fetch_object();
        echo "<option value='$street->id'>$street->title</option>";
    }
    echo '</select>'
        . ' Дом <input type="text" name="textHouse" id="textHouse">'
        . '<input type="submit" name="Submit" value="Скачать">'
        . ' <a href="https://fialka.tv/control">Сбросить</a></div>';

    get_footer();
}