<?php
function zonify_enqueue_styles() {
    wp_enqueue_style(
        'zonify-contact-style', // Handle unique
        plugins_url( '../assets/css/zonify-contact.css', __FILE__ ), // Chemin vers le fichier CSS
        array(), // Dépendances éventuelles
        '1.0.0' // Version
    );
}
add_action( 'wp_enqueue_scripts', 'zonify_enqueue_styles' );

function zonify_contact_form_shortcode( $atts ) {
    // Récupération de l'ID du commercial passé en GET
    $commercial_id = isset( $_GET['commercial_id'] ) ? intval( $_GET['commercial_id'] ) : 0;
    $commercial_name = '';
    $commercial_email = '';

    // Si un ID est fourni, on tente de récupérer le post correspondant
    if ( $commercial_id ) {
        $commercial_post = get_post( $commercial_id );
        if ( $commercial_post && $commercial_post->post_type == 'commercial' ) {
            $commercial_name = $commercial_post->post_title;
            $commercial_email = get_post_meta( $commercial_id, 'commercial_email', true );
        }


    }

    ob_start(); 

     // Affichage du popup en fonction du paramètre GET
     if ( isset( $_GET['contact_submitted'] ) ) {
        if ( $_GET['contact_submitted'] === 'true' ) {
            echo '<div id="contact-popup" class="contact-popup success">
                    <span class="close-popup">&times;</span>
                    <p>Votre message a été envoyé avec succès.</p>
                  </div>';
        } elseif ( $_GET['contact_submitted'] === 'error' ) {
            echo '<div id="contact-popup" class="contact-popup error">
                    <span class="close-popup">&times;</span>
                    <p>Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.</p>
                  </div>';
        }
    }
    ?>
    <form method="post" action="" class="zonify-contact-form">
        <?php wp_nonce_field( 'zonify_contact_nonce', 'zonify_contact_nonce_field' ); ?>
        <input type="hidden" name="commercial_id" value="<?php echo esc_attr( $commercial_id ); ?>" />
        
        <?php if ( $commercial_name ): ?>
        <p>
            <strong>Vous contactez <?php echo esc_html( $commercial_name ); ?></strong>
           
        </p>
        <?php endif; ?>
        
        <p>
            <label for="zonify_contact_name">Nom :</label>
            <input type="text" name="name" id="zonify_contact_name" required value="<?php echo esc_html( $commercial_name ); ?>"/>
        </p>
        
        <p>
            <label for="zonify_contact_email">Email :</label>
            <input type="email" name="email" id="zonify_contact_email" required value="<?php echo esc_html( $commercial_email ); ?>"/>
        </p>
        
        <p>
            <label for="zonify_contact_subject">Sujet :</label>
            <input type="text" name="subject" id="zonify_contact_subject" required value="<?php echo $commercial_name ? esc_attr( 'Demande de contact pour ' . $commercial_name ) : ''; ?>" />
        </p>
        
        <p>
            <label for="zonify_contact_message">Message :</label>
            <textarea name="message" id="zonify_contact_message" required></textarea>
        </p>
        
        <p>
            <input type="submit" name="zonify_submit_contact" value="Envoyer" />
        </p>
    </form>

    <script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var closeBtn = document.querySelector('.contact-popup .close-popup');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            var popup = this.parentElement;
            popup.style.display = 'none';
        });
    }
});

    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'zonify_contact_form', 'zonify_contact_form_shortcode' );
