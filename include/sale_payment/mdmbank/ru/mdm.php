<?
global $MESS;

$MESS["MDMB_DTITLE"] = "Оплата через MDM Bank";
$MESS["MDMB_DDESCR"] = "Оплата через MDM Bank";

$MESS["MDMB_AMOUNT"] = "Сумма (с десятичной точкой)";
$MESS["MDMB_CURRENCY"] = "Валюта: RUB – рубли,USD – доллары, EUR - евро";
$MESS["MDMB_ORDER"] = "Внутренний номер заказа (должен быть уникальным, используется для операции завершения расчета)";
$MESS["MDMB_DESC"] = "Описание заказа (желательно латиницей)";
$MESS["MDMB_MERCH_NAME"] = "Наименование торговой точки";
$MESS["MDMB_MERCH_URL"] = "URL, который подставляется по ссылке «Назад в магазин»";
$MESS["MDMB_MERCHANT"] = "Код торговца, присваиваемый Банком (необязательно)";
$MESS["MDMB_TERMINAL"] = "Код терминала, присваиваемый Банком";
$MESS["MDMB_EMAIL"] = "E-mail (если используется e-mail-уведомление об операции)";
$MESS["MDMB_TRTYPE"] = "Тип операции (0 – авторизация, 21 – завершение расчета, 22 – возврат/отмена авторизации)";
$MESS["MDMB_COUNTRY"] = "Страна. Для России - RU";
$MESS["MDMB_MERC_GMT"] = "Timezone мерчанта (аналогично установке в Unix, для Москвы +3)";
$MESS["MDMB_TIMESTAMP"] = "Время проведения операции в UTC (время Лондона) формат YYYYMMDDHHMMSS";
$MESS["MDMB_BACKREF"] = "URL для отправки CallBack-сообщения об операции. В настоящее не используется";
$MESS["MDMB_ACTION"] = "0 – Операция одобрена, 1 – Повторная транзакция, 2- Технические проблемы, 3 – Операция отклонена";
$MESS["MDMB_RC"] = "Код ответа (ISO8583)";
$MESS["MDMB_AUTH_CODE"] = "Код авторизации (может отсутствовать)";
$MESS["MDMB_RRN"] = "Retrieval reference number (уникальный идентификатор платежа в платежной системе)";
$MESS["MDMB_INT_REF"] = "Внутренний reference number операции";
$MESS["MDMB_CARD_ID"] = "Замаскированный номер карты";
?>
