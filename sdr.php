<?php

/**
 *
 */

if ( !defined('ABSPATH') ) {
	/** Set up WordPress environment */
	require_once( dirname( __FILE__ ) . '/wp-load.php' );
}

function get_seamless_donation_audit() {
    global $wpdb;
    $row = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."seamless_donations_audit WHERE created_on = %s ORDER BY changed_on ASC", '0000-00-00 00:00:00' ) );
    return $row;
}

try {

    //The name of the CSV file that will be downloaded by the user.
    $fileName = 'donation_receipts.csv';
    
    //Set the Content-Type and Content-Disposition headers.
    header('Content-Type: application/excel');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    
    //A multi-dimensional array containing our CSV data.
    $data = array(
        //Our header (optional).
        array("Nome", "Email", "Telefono", "Indirizzo", "Importo", "Giorno"),
    );

    $ay_donations = get_seamless_donation_audit();

    if (is_array($ay_donations)) {
        foreach ($ay_donations AS $donation) {
            //recupero i dati del donatore
            $ay_donation_details = unserialize($donation->option_value);
           
            $audit = array(
                $ay_donation_details['FIRSTNAME']." ".$ay_donation_details['LASTNAME'],
                $ay_donation_details['EMAIL'],
                $ay_donation_details['PHONE'],
                $ay_donation_details['ADDRESS'].", ".$ay_donation_details['CITY'].", ".$ay_donation_details['ZIP'].", ".$ay_donation_details['COUNTRY'],
                $ay_donation_details['AMOUNT'],
                $donation->changed_on,
            );

            array_push($data, $audit);
        }
    }

    //Open up a PHP output stream using the function fopen.
    $fp = fopen('php://output', 'w');
    
    //Loop through the array containing our CSV data.
    foreach ($data as $row) {
        //fputcsv formats the array into a CSV format.
        //It then writes the result to our output stream.
        fputcsv($fp, $row);
    }
    
    //Close the file handle.
    fclose($fp);



} catch (Exception $e) {
    echo "<pre>";
        print_r($e);
    echo "</pre>";
}



?>