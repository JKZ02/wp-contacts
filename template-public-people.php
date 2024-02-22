<?php
/*
Template Name: People List
*/

get_header();

global $wpdb;

$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

$sql = "SELECT * FROM person";
//If the search bar is not empty we check if the text in it matches either of the three: name, email or number
if (!empty($search_query)) {
    $sql .= $wpdb->prepare(" WHERE (name LIKE '%%%s%%' OR email LIKE '%%%s%%' OR ID IN (
            SELECT person FROM contact WHERE Number LIKE '%%%s%%'
        ))", $search_query, $search_query, $search_query);
}

$query = $wpdb->prepare($sql, $search_query, $search_query, $search_query);

$people = $wpdb->get_results($query);

?>
<div class="search-bar">
    <form method="get" action="">
        <input type="text" name="search" placeholder="Search by name, email, or number" class="search-input" value="<?php echo esc_attr($search_query); ?>">
        <button type="submit" class="search-button">Search</button>
    </form>
</div>
<?php
//List all the visible(not soft deleted) people returned from the db
if ($people) {
    echo '<div class="people-list">';
    foreach ($people as $person) {
        echo '<div class="person">';
        echo '<h2>' . esc_html($person->name) . '</h2>';
        echo '<p>Email: ' . esc_html($person->email) . '</p>';
        
        $contacts = $wpdb->get_results($wpdb->prepare("SELECT * FROM contact WHERE person = %d", $person->ID));
        //contacts toggle for better readability
        if ($contacts) {
            echo '<button class="toggle-contacts-btn" onclick="toggleContacts(this)">Contacts</button>';
            echo '<div class="contacts-list" style="display: none;">';
            echo '<ul>';
            foreach ($contacts as $contact) {
                echo '<li>+' . esc_html($contact->CountryCode) . ' ' . esc_html($contact->Number) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<p>No contacts found.</p>';
        }
        
        echo '</div>';
    }
    echo '</div>';
} else {
    echo '<p>No people found.</p>';
}

?>
<script>
    function toggleContacts(button) {
        var contactsList = button.nextElementSibling;
        if (contactsList.style.display === "none") {
            contactsList.style.display = "block";
        } else {
            contactsList.style.display = "none";
        }
    }
</script>
<?php

get_footer();
?>
