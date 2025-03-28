<?php
function zonify_handle_contact_form() {
    if ( isset( $_POST['zonify_submit_contact'] ) && isset( $_POST['zonify_contact_nonce_field'] ) ) {
        // Vérification du nonce pour la sécurité
        if ( ! wp_verify_nonce( $_POST['zonify_contact_nonce_field'], 'zonify_contact_nonce' ) ) {
            return;
        }
        
        // Nettoyage et récupération des données
        $name         = sanitize_text_field( $_POST['name'] );
        $email        = sanitize_email( $_POST['email'] );
        $subject      = sanitize_text_field( $_POST['subject'] );
        $message      = sanitize_textarea_field( $_POST['message'] );
        $commercial_id = isset( $_POST['commercial_id'] ) ? intval( $_POST['commercial_id'] ) : 0;
        
        // Insertion du contact sous forme de post
        $post_id = wp_insert_post( array(
            'post_type'    => 'zonify_contact',
            'post_title'   => $subject,
            'post_content' => $message,
            'post_status'  => 'publish',
        ) );
        
        if ( $post_id ) {
            // Sauvegarde des métadonnées associées
            update_post_meta( $post_id, 'zonify_contact_name', $name );
            update_post_meta( $post_id, 'zonify_contact_email', $email );
            update_post_meta( $post_id, 'zonify_commercial_id', $commercial_id );
            
            // Récupération de l'email du commercial via son CPT ou via ses métadonnées
            $commercial_email = get_post_meta( $commercial_id, 'email', true );
            
            if ( $commercial_email ) {
                // Préparation du contenu de l'email
                $mail_subject = 'Nouveau contact : ' . $subject;
                $mail_message = "Vous avez reçu un nouveau message de " . $name . " (" . $email . "):\n\n" . $message;
                
                // Envoi de l'email et capture du résultat
                $mail_sent = wp_mail( $commercial_email, $mail_subject, $mail_message );
                
                if ( $mail_sent ) {
                    // Le mail a été accepté pour l'envoi
                    // Vous pouvez éventuellement définir un message de succès à afficher sur le front-end
                } else {
                    // Le mail n'a pas été accepté, enregistrez l'erreur pour le débogage
                    error_log( "Échec de l'envoi de l'email à {$commercial_email} pour le contact ID {$post_id}" );
                    // Vous pouvez aussi définir un message d'erreur pour l'utilisateur
                }
            }
            
            // Redirection ou affichage d'un message de confirmation
            wp_redirect( add_query_arg( 'contact_submitted', 'true', get_permalink() ) );
            exit;
        }
    }
}
add_action( 'init', 'zonify_handle_contact_form' );
