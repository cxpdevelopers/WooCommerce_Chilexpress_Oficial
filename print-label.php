<?php
if (!class_exists('FPDF')) {
    include plugin_dir_path( __FILE__ ) . '/includes/fpdf.php';
}
global $order_label;
if (!is_user_logged_in()) {
    echo "Not Authorized";
    die();
} else if(!current_user_can('edit_shop_orders')){
    echo "Not Authorized";
    die();
}
if(!isset($order_label)){
    die("Invalid Order Id");
}

$order_id = intval($order_label);
if (!$order_id && $order_id < 0) {
    die("Invalid Order Id");
}
$order = wc_get_order( $order_id );
if (!$order) {
    die("Order Not found");
}

$transportOrderNumbers = $order->get_meta( 'transportOrderNumbers');
$labelsData = $order->get_meta( 'labelsData');

if (is_array($transportOrderNumbers)) {
    $pdf = new FPDF();
    $out = array();
    for($i = 0; $i <count($transportOrderNumbers); $i++) {
        $base_image = $labelsData[$i];
        $pdf->AddPage('L', array(85, 150));
        $pic = 'data://text/plain;base64,'. $base_image;
        $pdf->Image($pic, 10, 10, 0, 0,'jpg');
    }
    $fileName = 'Orden_de_transporte_' . $transportOrderNumbers[0] . '.pdf';
    $pdf->Output($fileName, 'D');
}


