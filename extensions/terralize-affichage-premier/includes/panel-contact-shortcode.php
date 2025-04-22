<?php
/**
 * Shortcode pour le formulaire de réservation de panneaux
 * Auteur: Terralize
 */

// Shortcode pour afficher le formulaire de réservation
function terralize_ap_panel_contact_form_shortcode($atts) {
    // Extraction des données du panneau depuis l'URL
    $panel_id = isset($_GET['panel_id']) ? intval($_GET['panel_id']) : 0;
    $panel_ref = isset($_GET['panel_ref']) ? sanitize_text_field($_GET['panel_ref']) : '';
    $panel_type = isset($_GET['panel_type']) ? sanitize_text_field($_GET['panel_type']) : '';
    $panel_address = isset($_GET['panel_address']) ? sanitize_text_field($_GET['panel_address']) : '';
    $panel_city = isset($_GET['panel_city']) ? sanitize_text_field($_GET['panel_city']) : '';
    $panel_format = isset($_GET['panel_format']) ? sanitize_text_field($_GET['panel_format']) : '';
    
    // Récupérer les détails supplémentaires du panneau si on a l'ID
    if ($panel_id > 0) {
        $panel_post = get_post($panel_id);
        if ($panel_post) {
            // Si pas de référence dans l'URL, on essaie de la récupérer
            if (empty($panel_ref)) {
                $panel_ref = get_post_meta($panel_id, 'panel_reference', true);
            }
            
            // Si le titre n'est pas déjà défini, on le récupère
            $panel_title = $panel_post->post_title;
        }
    }

    // Vérifier si le formulaire a été soumis
    $form_submitted = false;
    $submission_error = false;
    $submission_message = '';
    
    if (isset($_POST['terralize_ap_panel_contact_submit']) && wp_verify_nonce($_POST['terralize_ap_contact_nonce'], 'terralize_ap_panel_contact')) {
        // Récupérer les données du formulaire
        $contact_name = isset($_POST['contact_name']) ? sanitize_text_field($_POST['contact_name']) : '';
        $contact_company = isset($_POST['contact_company']) ? sanitize_text_field($_POST['contact_company']) : '';
        $contact_email = isset($_POST['contact_email']) ? sanitize_email($_POST['contact_email']) : '';
        $contact_phone = isset($_POST['contact_phone']) ? sanitize_text_field($_POST['contact_phone']) : '';
        $contact_message = isset($_POST['contact_message']) ? sanitize_textarea_field($_POST['contact_message']) : '';
        $contact_start_date = isset($_POST['contact_start_date']) ? sanitize_text_field($_POST['contact_start_date']) : '';
        $contact_duration = isset($_POST['contact_duration']) ? intval($_POST['contact_duration']) : 0;
        
        // Validation de base
        if (empty($contact_name) || empty($contact_email) || empty($contact_message)) {
            $submission_error = true;
            $submission_message = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            // Préparer les entêtes et le corps du mail
            $to = get_option('admin_email');
            $subject = 'Demande de réservation de panneau - Réf: ' . $panel_ref;
            
            $body = "Demande de réservation pour le panneau :\n\n";
            $body .= "Référence : " . $panel_ref . "\n";
            if (!empty($panel_title)) {
                $body .= "Titre : " . $panel_title . "\n";
            }
            if (!empty($panel_type)) {
                $body .= "Type : " . $panel_type . "\n";
            }
            if (!empty($panel_format)) {
                $body .= "Format : " . $panel_format . "\n";
            }
            if (!empty($panel_address)) {
                $body .= "Adresse : " . $panel_address . "\n";
            }
            if (!empty($panel_city)) {
                $body .= "Ville : " . $panel_city . "\n";
            }
            
            $body .= "\nInformations du contact :\n\n";
            $body .= "Nom : " . $contact_name . "\n";
            $body .= "Société : " . $contact_company . "\n";
            $body .= "Email : " . $contact_email . "\n";
            $body .= "Téléphone : " . $contact_phone . "\n";
            
            $body .= "\nDétails de la réservation :\n\n";
            if (!empty($contact_start_date)) {
                $body .= "Date de début souhaitée : " . $contact_start_date . "\n";
            }
            if ($contact_duration > 0) {
                $body .= "Durée de la campagne : " . $contact_duration . " semaine(s)\n";
            }
            
            $body .= "\nMessage :\n" . $contact_message . "\n";
            
            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'From: ' . $contact_name . ' <' . $contact_email . '>',
                'Reply-To: ' . $contact_email
            );
            
            // Envoyer l'email
            $mail_sent = wp_mail($to, $subject, $body, $headers);
            
            if ($mail_sent) {
                $form_submitted = true;
                $submission_message = 'Votre demande de réservation a bien été envoyée. Nous vous contacterons dans les plus brefs délais.';
                
                // Enregistrer également la demande comme post personnalisé si nécessaire
                if (post_type_exists('panel_request')) {
                    $request_data = array(
                        'post_title'    => 'Demande - ' . $panel_ref . ' - ' . $contact_name,
                        'post_content'  => $contact_message,
                        'post_status'   => 'publish',
                        'post_type'     => 'panel_request',
                    );
                    
                    $request_id = wp_insert_post($request_data);
                    
                    if ($request_id) {
                        // Enregistrer les métadonnées
                        update_post_meta($request_id, 'panel_id', $panel_id);
                        update_post_meta($request_id, 'panel_ref', $panel_ref);
                        update_post_meta($request_id, 'contact_name', $contact_name);
                        update_post_meta($request_id, 'contact_company', $contact_company);
                        update_post_meta($request_id, 'contact_email', $contact_email);
                        update_post_meta($request_id, 'contact_phone', $contact_phone);
                        update_post_meta($request_id, 'contact_start_date', $contact_start_date);
                        update_post_meta($request_id, 'contact_duration', $contact_duration);
                    }
                }
            } else {
                $submission_error = true;
                $submission_message = 'Une erreur s\'est produite lors de l\'envoi de votre demande. Veuillez réessayer ou nous contacter directement.';
            }
        }
    }
    
    // Commencer la capture de sortie du formulaire
    ob_start();
    
    // Afficher le message de confirmation ou d'erreur
    if ($form_submitted || $submission_error) {
        echo '<div class="terralize_ap-panel-contact-message ' . ($submission_error ? 'error' : 'success') . '">';
        echo $submission_message;
        echo '</div>';
    }
    
    // Si le formulaire a été soumis avec succès, ne pas réafficher le formulaire
    if (!$form_submitted) {
        ?>
        <div class="terralize_ap-panel-contact-form-container">
            <?php if (!empty($panel_ref) || !empty($panel_id)) : ?>
                <div class="terralize_ap-panel-contact-header">
                    <h3>Demande de réservation de panneau</h3>
                    <div class="terralize_ap-panel-details">
                        <?php if (!empty($panel_ref)) : ?>
                            <p><strong>Référence:</strong> <?php echo esc_html($panel_ref); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($panel_type)) : ?>
                            <p><strong>Type:</strong> <?php echo esc_html($panel_type); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($panel_format)) : ?>
                            <p><strong>Format:</strong> <?php echo esc_html($panel_format); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($panel_address)) : ?>
                            <p><strong>Localisation:</strong> <?php echo esc_html($panel_address); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="terralize_ap-panel-contact-header">
                    <h3>Demande de réservation de panneau</h3>
                    <p>Aucun panneau spécifique sélectionné. Veuillez préciser votre demande dans le message.</p>
                </div>
            <?php endif; ?>
            
            <form method="post" class="terralize_ap-panel-contact-form">
                <?php wp_nonce_field('terralize_ap_panel_contact', 'terralize_ap_contact_nonce'); ?>
                
                <input type="hidden" name="panel_id" value="<?php echo esc_attr($panel_id); ?>">
                <input type="hidden" name="panel_ref" value="<?php echo esc_attr($panel_ref); ?>">
                
                <div class="terralize_ap-form-section">
                    <h4>Vos coordonnées</h4>
                    
                    <div class="terralize_ap-form-row">
                        <div class="terralize_ap-form-col">
                            <label for="contact_name">Nom et prénom <span class="required">*</span></label>
                            <input type="text" name="contact_name" id="contact_name" required>
                        </div>
                        
                        <div class="terralize_ap-form-col">
                            <label for="contact_company">Société</label>
                            <input type="text" name="contact_company" id="contact_company">
                        </div>
                    </div>
                    
                    <div class="terralize_ap-form-row">
                        <div class="terralize_ap-form-col">
                            <label for="contact_email">E-mail <span class="required">*</span></label>
                            <input type="email" name="contact_email" id="contact_email" required>
                        </div>
                        
                        <div class="terralize_ap-form-col">
                            <label for="contact_phone">Téléphone</label>
                            <input type="tel" name="contact_phone" id="contact_phone">
                        </div>
                    </div>
                </div>
                
                <div class="terralize_ap-form-section">
                    <h4>Détails de votre projet</h4>
                    
                    <div class="terralize_ap-form-row">
                        <div class="terralize_ap-form-col">
                            <label for="contact_start_date">Date de début souhaitée</label>
                            <input type="text" name="contact_start_date" id="contact_start_date" class="datepicker" placeholder="JJ/MM/AAAA">
                        </div>
                        
                        <div class="terralize_ap-form-col">
                            <label for="contact_duration">Durée de la campagne (semaines)</label>
                            <select name="contact_duration" id="contact_duration">
                                <option value="0">- Sélectionner -</option>
                                <option value="1">1 semaine</option>
                                <option value="2">2 semaines</option>
                                <option value="4">1 mois</option>
                                <option value="8">2 mois</option>
                                <option value="12">3 mois</option>
                                <option value="24">6 mois</option>
                                <option value="52">1 an</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="terralize_ap-form-row">
                        <div class="terralize_ap-form-col full-width">
                            <label for="contact_message">Votre demande <span class="required">*</span></label>
                            <textarea name="contact_message" id="contact_message" rows="5" required></textarea>
                            <p class="field-help">Précisez les détails de votre projet, vos besoins spécifiques ou toute question concernant ce panneau.</p>
                        </div>
                    </div>
                </div>
                
                <div class="terralize_ap-form-submit">
                    <p class="consent-text">En soumettant ce formulaire, vous acceptez que les informations saisies soient utilisées pour traiter votre demande.</p>
                    <button type="submit" name="terralize_ap_panel_contact_submit" class="terralize_ap-submit-button">Envoyer ma demande</button>
                </div>
            </form>
        </div>
        
        <style>
            .terralize_ap-panel-contact-form-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background: #f9f9f9;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .terralize_ap-panel-contact-header {
                margin-bottom: 25px;
                padding-bottom: 15px;
                border-bottom: 1px solid #e5e5e5;
            }
            
            .terralize_ap-panel-contact-header h3 {
                color: #70c141;
                margin-top: 0;
                margin-bottom: 15px;
            }
            
            .terralize_ap-panel-details {
                background: #fff;
                padding: 15px;
                border-radius: 4px;
                border-left: 4px solid #70c141;
            }
            
            .terralize_ap-panel-details p {
                margin: 5px 0;
            }
            
            .terralize_ap-form-section {
                margin-bottom: 25px;
            }
            
            .terralize_ap-form-section h4 {
                color: #333;
                margin-bottom: 15px;
                padding-bottom: 5px;
                border-bottom: 1px solid #e5e5e5;
            }
            
            .terralize_ap-form-row {
                display: flex;
                flex-wrap: wrap;
                margin: 0 -10px 15px;
            }
            
            .terralize_ap-form-col {
                flex: 1;
                padding: 0 10px;
                min-width: 200px;
            }
            
            .terralize_ap-form-col.full-width {
                flex-basis: 100%;
            }
            
            .terralize_ap-panel-contact-form label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            
            .terralize_ap-panel-contact-form input[type="text"],
            .terralize_ap-panel-contact-form input[type="email"],
            .terralize_ap-panel-contact-form input[type="tel"],
            .terralize_ap-panel-contact-form select,
            .terralize_ap-panel-contact-form textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
            }
            
            .terralize_ap-panel-contact-form select {
                height: 40px;
            }
            
            .terralize_ap-panel-contact-form textarea {
                resize: vertical;
            }
            
            .field-help {
                font-size: 13px;
                color: #666;
                margin-top: 3px;
            }
            
            .required {
                color: #e74c3c;
            }
            
            .terralize_ap-form-submit {
                text-align: center;
                margin-top: 30px;
            }
            
            .consent-text {
                font-size: 13px;
                color: #666;
                margin-bottom: 15px;
            }
            
            .terralize_ap-submit-button {
                background-color: #70c141;
                color: white;
                padding: 12px 30px;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            
            .terralize_ap-submit-button:hover {
                background-color: #5ba535;
            }
            
            .terralize_ap-panel-contact-message {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
                text-align: center;
            }
            
            .terralize_ap-panel-contact-message.success {
                background-color: #dff2bf;
                color: #4f8a10;
                border: 1px solid #4f8a10;
            }
            
            .terralize_ap-panel-contact-message.error {
                background-color: #ffbaba;
                color: #d8000c;
                border: 1px solid #d8000c;
            }
            
            @media (max-width: 768px) {
                .terralize_ap-form-row {
                    flex-direction: column;
                }
                
                .terralize_ap-form-col {
                    margin-bottom: 10px;
                }
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Initialiser le datepicker pour la date de début
            if ($.fn.datepicker) {
                $('#contact_start_date').datepicker({
                    dateFormat: 'dd/mm/yy',
                    minDate: 0,
                    changeMonth: true,
                    changeYear: true
                });
            }
        });
        </script>
        <?php
    }
    
    // Récupérer le contenu capturé et le retourner
    return ob_get_clean();
}

// Enregistrer le shortcode
add_shortcode('terralize_panel_contact_form', 'terralize_ap_panel_contact_form_shortcode');

// Créer le type de post personnalisé pour les demandes de réservation
function terralize_ap_register_panel_request_post_type() {
    $labels = array(
        'name'               => 'Demandes de réservation',
        'singular_name'      => 'Demande de réservation',
        'menu_name'          => 'Demandes de réservation',
        'name_admin_bar'     => 'Demande de réservation',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter une demande',
        'new_item'           => 'Nouvelle demande',
        'edit_item'          => 'Modifier la demande',
        'view_item'          => 'Voir la demande',
        'all_items'          => 'Toutes les demandes',
        'search_items'       => 'Rechercher des demandes',
        'not_found'          => 'Aucune demande trouvée',
        'not_found_in_trash' => 'Aucune demande trouvée dans la corbeille'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-megaphone',
        'query_var'          => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor')
    );

    register_post_type('panel_request', $args);
}
add_action('init', 'terralize_ap_register_panel_request_post_type');

// Ajouter les métaboxes pour les détails de la demande
function terralize_ap_add_panel_request_meta_boxes() {
    add_meta_box(
        'panel_request_details',
        'Détails de la demande',
        'terralize_ap_panel_request_details_callback',
        'panel_request',
        'normal',
        'high'
    );
    
    add_meta_box(
        'panel_request_contact',
        'Informations du contact',
        'terralize_ap_panel_request_contact_callback',
        'panel_request',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'terralize_ap_add_panel_request_meta_boxes');

// Callback pour la métabox des détails du panneau
function terralize_ap_panel_request_details_callback($post) {
    $panel_id = get_post_meta($post->ID, 'panel_id', true);
    $panel_ref = get_post_meta($post->ID, 'panel_ref', true);
    $start_date = get_post_meta($post->ID, 'contact_start_date', true);
    $duration = get_post_meta($post->ID, 'contact_duration', true);
    
    echo '<div class="terralize_ap-meta-field-row">';
    echo '<div class="terralize_ap-meta-field">';
    echo '<label>ID du panneau:</label>';
    echo '<div>' . esc_html($panel_id) . '</div>';
    echo '</div>';
    
    echo '<div class="terralize_ap-meta-field">';
    echo '<label>Référence du panneau:</label>';
    echo '<div>' . esc_html($panel_ref) . '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="terralize_ap-meta-field-row">';
    echo '<div class="terralize_ap-meta-field">';
    echo '<label>Date de début souhaitée:</label>';
    echo '<div>' . esc_html($start_date) . '</div>';
    echo '</div>';
    
    echo '<div class="terralize_ap-meta-field">';
    echo '<label>Durée de la campagne:</label>';
    echo '<div>' . ($duration ? esc_html($duration) . ' semaine(s)' : 'Non spécifiée') . '</div>';
    echo '</div>';
    echo '</div>';
    
    // Lien vers le panneau dans l'admin
    if ($panel_id) {
        echo '<a href="' . esc_url(get_edit_post_link($panel_id)) . '" class="button" target="_blank">Voir le panneau dans l\'admin</a>';
    }
}

// Callback pour la métabox des informations de contact
function terralize_ap_panel_request_contact_callback($post) {
    $contact_name = get_post_meta($post->ID, 'contact_name', true);
    $contact_company = get_post_meta($post->ID, 'contact_company', true);
    $contact_email = get_post_meta($post->ID, 'contact_email', true);
    $contact_phone = get_post_meta($post->ID, 'contact_phone', true);
    
    echo '<div class="terralize_ap-meta-field-row">';
    echo '<div class="terralize_ap-meta-field">';
    echo '<label>Nom:</label>';
    echo '<div>' . esc_html($contact_name) . '</div>';
    echo '</div>';
    
    echo '<div class="terralize_ap-meta-field">';
    echo '<label>Société:</label>';
    echo '<div>' . esc_html($contact_company) . '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="terralize_ap-meta-field-row">';
    echo '<div class="terralize_ap-meta-field">';
    echo '<label>Email:</label>';
    echo '<div><a href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . '</a></div>';
    echo '</div>';
    
    echo '<div class="terralize_ap-meta-field">';
    echo '<label>Téléphone:</label>';
    echo '<div>' . esc_html($contact_phone) . '</div>';
    echo '</div>';
    echo '</div>';
    
    // Bouton pour répondre par email
    echo '<a href="mailto:' . esc_attr($contact_email) . '?subject=Re: Demande de réservation - Réf: ' . esc_attr($panel_ref) . '" class="button">';
    echo 'Répondre par email';
    echo '</a>';
}

// Styles admin pour les métaboxes
function terralize_ap_panel_request_admin_styles() {
    $screen = get_current_screen();
    
    if ($screen && $screen->post_type === 'panel_request') {
        echo '<style>
            .terralize_ap-meta-field-row {
                display: flex;
                margin-bottom: 15px;
            }
            
            .terralize_ap-meta-field {
                flex: 1;
                margin-right: 20px;
            }
            
            .terralize_ap-meta-field:last-child {
                margin-right: 0;
            }
            
            .terralize_ap-meta-field label {
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .terralize_ap-meta-field div {
                padding: 8px;
                background: #f9f9f9;
                border: 1px solid #e5e5e5;
                border-radius: 3px;
            }
        </style>';
    }
}
add_action('admin_head', 'terralize_ap_panel_request_admin_styles'); 