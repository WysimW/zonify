<?php
function zonify_import_csv(){
    if(!current_user_can('manage_options')){
        wp_die('Permission refusée');
    }
    check_admin_referer('zonify_import_csv_nonce');

    if(empty($_FILES['zones_csv']['tmp_name'])){
        wp_die('Aucun fichier CSV fourni.');
    }

    $handle = fopen($_FILES['zones_csv']['tmp_name'],'r');
    if(!$handle){
        wp_die('Impossible de lire le fichier CSV.');
    }

    $header = fgetcsv($handle,0,',');
    if(!$header){
        wp_die('CSV vide ou illisible');
    }

    $col_zone_id     = array_search('zone_id',$header);
    $col_zone_title  = array_search('zone_title',$header);
    $col_com_id      = array_search('commercial_id',$header);
    $col_geo         = array_search('geojson',$header);
    if($col_zone_id===false || $col_geo===false){
        wp_die('Colonnes manquantes dans le CSV (zone_id, geojson...)');
    }

    $count_created=0;$count_updated=0;
    while(($row=fgetcsv($handle,0,','))!==false){
        $zid   = !empty($row[$col_zone_id])?intval($row[$col_zone_id]):0;
        $ztitle= !empty($col_zone_title)?sanitize_text_field($row[$col_zone_title]):'Zone importée';
        $com_id= !empty($col_com_id)?intval($row[$col_com_id]):0;
        $geometry= isset($row[$col_geo])?$row[$col_geo]:'';

        $test=json_decode($geometry,true);
        if(!$test) continue; // geojson invalide

        if($zid && get_post_type($zid)==='zone'){
            wp_update_post(array('ID'=>$zid,'post_title'=>$ztitle));
            update_post_meta($zid,'zone_geojson',$geometry);
            update_post_meta($zid,'zone_commercial_id',$com_id);
            $count_updated++;
        } else {
            $new_id=wp_insert_post(array(
                'post_type'=>'zone',
                'post_title'=>$ztitle,
                'post_status'=>'publish'
            ));
            if($new_id){
                update_post_meta($new_id,'zone_geojson',$geometry);
                update_post_meta($new_id,'zone_commercial_id',$com_id);
                $count_created++;
            }
        }
    }
    fclose($handle);

    wp_redirect(admin_url('admin.php?page=zonify_import_export&csv_import_done=1&created='.$count_created.'&updated='.$count_updated));
    exit;
}
add_action('admin_post_zonify_import_csv','zonify_import_csv');