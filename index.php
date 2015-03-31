<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', 1);
header("Content-Type: text/html; charset=utf-8");

$project_root = $_SERVER['DOCUMENT_ROOT'];
$smarty_dir = $project_root . '/smarty/';

// put full path to Smarty.class.php
require($smarty_dir . '/libs/Smarty.class.php');
$smarty = new Smarty();

$smarty->compile_check = true;
$smarty->debugging = false;

$smarty->template_dir = $smarty_dir . 'templates';
$smarty->compile_dir = $smarty_dir . 'templates_c';
$smarty->cache_dir = $smarty_dir . 'cache';
$smarty->config_dir = $smarty_dir . 'configs';

// Functions

function getListOfExplanations($explanation) {
    $list = array();
    foreach ($explanation as $key => $value) {
        $list[] = '<a href="index.php?show=' . $key . '">' . $value['title'] . '</a>';
        $list[] = $value['price'];
        $list[] = $value['seller_name'];
        $list[] = '<a href="index.php?delete=' . $key . '">Удалить</a>';
    }
    return $list;
}

function processingQuery($array) {
    foreach ($array as $key => &$value) {
        $query[$key] = trim(htmlspecialchars($value), ' .,\|/*-+');
    }
    $query['price'] = (float) $query['price'];
    return $query;
}

function addExplanation(&$explanation, $id, $array, $filename = 'explanation.php') {
    if ($id == '') {
        $explanation[] = $array;
    } else {
        $explanation[$id] = $array;
    }
    if (!file_put_contents($filename, serialize($explanation))) {
        exit('Ошибка: не удалось записать фаил ' . $filename);
    }
}

function deleteExplanation(&$explanation, $name, $filename = 'explanation.php') {
    unset($explanation[$name]);
    if (!file_put_contents($filename, serialize($explanation))) {
        exit('Ошибка: не удалось записать фаил ' . $filename);
    }
}

// Main block

$explanation = array();
$filename = 'explanation.php';

if (file_exists($filename)) {
    if (!file_get_contents($filename)) {
        exit('Ошибка: неверный формат файла ' . $filename);
    }
    $explanation = unserialize(file_get_contents($filename));
}

if (isset($_GET['show'])) {
    $show = $_GET['show'];
    $smarty->assign('header_tpl', 'header_exp');
    $smarty->assign('title', 'Объявление');
} else {
    $show = '';
    $smarty->assign('header_tpl', 'header');
    $smarty->assign('title', 'Доска объявлений');
}
$id = (isset($_GET['id'])) ? $_GET['id'] : '';

$cities = array('641780' => 'Новосибирск', '641490' => 'Барабинск', '641510' => 'Бердск', '641600' => 'Искитим', '641630' => 'Колывань', '641680' => 'Краснообск', '641710' => 'Куйбышев', '641760' => 'Мошково', '641790' => 'Обь', '641800' => 'Ордынское', '641970' => 'Черепаново',);
$categories = array(
    'Транспорт' => array('9' => 'Автомобили с пробегом', '109' => 'Новые автомобили', '14' => 'Мотоциклы и мототехника', '81' => 'Грузовики и спецтехника', '11' => 'Водный транспорт', '10' => 'Запчасти и аксессуары'),
    'Недвижимость' => array('24' => 'Квартиры', '23' => 'Комнаты', '25' => 'Дома, дачи, коттеджи', '26' => 'Земельные участки', '85' => 'Гаражи и машиноместа', '42' => 'Коммерческая недвижимость', '86' => 'Недвижимость за рубежом'),
    'Работа' => array('111' => 'Вакансии (поиск сотрудников)', '112' => 'Резюме (поиск работы)'),
    'Услуги' => array('114' => 'Предложения услуг', '115' => 'Запросы на услуги'),
    'Личные вещи' => array('27' => 'Одежда, обувь, аксессуары', '29' => 'Детская одежда и обувь', '30' => 'Товары для детей и игрушки', '28' => 'Часы и украшения', '88' => 'Красота и здоровье'),
    'Для дома и дачи' => array('21' => 'Бытовая техника', '20' => 'Мебель и интерьер', '87' => 'Посуда и товары для кухни', '82' => 'Продукты питания', '19' => 'Ремонт и строительство', '106' => 'Растения'),
    'Бытовая техника' => array('32' => 'Аудио и видео', '97' => 'Игры, приставки и программы', '31' => 'Настольные компьютеры', '98' => 'Ноутбуки', '99' => 'Оргтехника и расходники', '96' => 'Планшеты и электронные книги', '84' => 'Телефоны', '101' => 'Товары для компьютера', '105' => 'Фототехника'),
    'Хобби и отдых' => array('33' => 'Билеты и путешествия', '34' => 'Велосипеды', '83' => 'Книги и журналы', '36' => 'Коллекционирование', '38' => 'Музыкальные инструменты', '102' => 'Охота и рыбалка', '39' => 'Спорт и отдых', '103' => 'Знакомства'),
    'Животные' => array('89' => 'Собаки', '90' => 'Кошки', '91' => 'Птицы', '92' => 'Аквариум', '93' => 'Другие животные', '94' => 'Товары для животных'),
    'Для бизнеса' => array('116' => 'Готовый бизнес', '40' => 'Оборудование для бизнеса'));
$name = (isset($explanation[$show])) ? $explanation[$show] :
        array('private' => '0', 'seller_name' => '', 'email' => '', 'allow_mails' => '',
    'phone' => '', 'location_id' => '', 'category_id' => '', 'title' => '', 'description' => '',
    'price' => '0');

if (isset($_GET['delete'])) {
    deleteExplanation($explanation, $_GET['delete']);
}

if (isset($_POST['button_add'])) {
    $query = processingQuery($_POST);
    addExplanation($explanation, $id, $query);
}

$listOfExplanations = getListOfExplanations($explanation);

$smarty->assign('name', $name);
$smarty->assign('id', $id);
$smarty->assign('show', $show);
$smarty->assign('private_radios', array('0' => 'Частное лицо', '1' => 'Компания'));
$smarty->assign('cities', $cities);
$smarty->assign('categories', $categories);
$smarty->assign('list', $listOfExplanations);
$smarty->assign('tr', array('bgcolor="#ffffff"', 'bgcolor="#E7F5FE"'));

$smarty->display('index.tpl');
