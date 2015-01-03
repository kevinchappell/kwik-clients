<?php

class K_CLIENTS_HELPERS extends KwikClients{

  function __construct() {
      $this->name = "K_CLIENTS_HELPERS";
   }

  public function array_insert_at_position($array, $values, $pivot, $position = 'after'){

    $offset = 0;
    foreach($array as $key => $value){
      ++$offset;
      if ($key == $pivot){
        break;
      }
    }

    if ($position == 'before'){
      --$offset;
    }

    return array_slice($array, 0, $offset, TRUE) + $values + array_slice($array, $offset, NULL, TRUE);
  }

  // ADD NEW COLUMN
  public function add_clients_columns($columns) {
    $columns = self::array_insert_at_position($columns, array('featured_image' => __('Image')), 'cb');
    return $columns;
  }

  public function k_client_logo_text_filter( $translated_text, $untranslated_text, $domain ) {
    global $post, $typenow, $current_screen;

    if( is_admin() && 'clients' === $typenow )  {
      switch( $untranslated_text ) {

        case 'Insert into post':
          $translated_text = __( 'Add to Client description','kwik' );
        break;

        case 'Set featured image':
          $translated_text = __( 'Set Client logo','kwik' );
        break;

        case 'Set Featured Image':
          $translated_text = __( 'Set Client Logo','kwik' );
        break;

        case 'Featured Image':
          $translated_text = __( 'Client Logo','kwik' );
        break;

        case 'Enter title here':
          $translated_text = __( 'Enter Client Name','kwik' );
        break;
       }
    }
    return $translated_text;
  }


  public function clients_at_a_glance(){
    Kwikutils::cpt_at_a_glance('clients');
  }


}