<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Daten aus dem Formular lesen
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));

    // E-Mail-Empfänger und Betreff
    $to = "deine-email@beispiel.com";
    $email_subject = "Kontaktformular: $subject";

    // Nachricht zusammenstellen
    $email_body = "Name: $name\n";
    $email_body .= "E-Mail: $email\n\n";
    $email_body .= "Nachricht:\n$message\n";

    // E-Mail-Header
    $headers = "From: $email\n";
    $headers .= "Reply-To: $email";

    // E-Mail senden
    if (mail($to, $email_subject, $email_body, $headers)) {
        echo "Vielen Dank für Ihre Nachricht. Wir werden uns in Kürze bei Ihnen melden.";
    } else {
        echo "Entschuldigung, es gab ein Problem beim Versenden Ihrer Nachricht. Bitte versuchen Sie es später erneut.";
    }
} else {
    echo "Ungültige Anfrage.";
}
?>
