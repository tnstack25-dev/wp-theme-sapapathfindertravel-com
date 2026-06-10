<?php
/**
 * Template Name: Contact
 *
 * @package Flatsome_Child
 */

get_header();

$contact_bg = get_the_post_thumbnail_url(get_the_ID(), 'full');
$office_address = 'S&#7889; 10, &#272;&#432;&#7901;ng Ho&agrave;ng Di&#7879;u, Ph&#432;&#7901;ng Sapa, T&#7881;nh L&agrave;o Cai, Vi&#7879;t Nam';
$office_map_query = rawurlencode('So 10 Duong Hoang Dieu, Sa Pa, Lao Cai, Vietnam');

function sapa_contact_icon($name)
{
    $icons = array(
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.12.91.33 1.79.62 2.64a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.44-1.14a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.64.62A2 2 0 0 1 22 16.92z"/>',
        'message' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>',
        'mail' => '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="m22 6-10 7L2 6"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 0 20"/><path d="M12 2a15.3 15.3 0 0 0 0 20"/>',
        'map' => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'arrow' => '<path d="M7 17 17 7"/><path d="M7 7h10v10"/>',
        'building' => '<path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-3"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M9 17h.01"/>',
    );

    if (empty($icons[$name])) {
        return '';
    }

    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[$name] . '</svg>';
}
?>

<main id="main" class="sapa-contact-page sapa-contact-pro">
    <section class="sapa-contact-hero">
        <div class="sapa-contact-bg"<?php echo $contact_bg ? ' style="--sapa-contact-bg: url(' . esc_url($contact_bg) . ');"' : ''; ?> aria-hidden="true"></div>
        <div class="sapa-contact-orbit" aria-hidden="true"></div>

        <div class="sapa-contact-inner">
            <div class="sapa-contact-hero-copy">
                <span class="sapa-contact-kicker">Contact us</span>
                <h1>Sapa Pathfinder Travel</h1>
                <p>Tell us where you want to go. Our local team in Sapa will help you plan a clear, flexible and memorable trip.</p>

                <div class="sapa-contact-actions">
                    <a class="sapa-contact-btn sapa-contact-btn-primary" href="tel:0919736787">
                        <?php echo sapa_contact_icon('phone'); ?>
                        Call now
                    </a>
                    <a class="sapa-contact-btn" href="https://wa.me/84985768359">
                        <?php echo sapa_contact_icon('message'); ?>
                        WhatsApp
                    </a>
                </div>
            </div>

            <aside class="sapa-contact-feature-card">
                <div class="sapa-contact-feature-icon"><?php echo sapa_contact_icon('building'); ?></div>
                <span>Company</span>
                <h2>C&ocirc;ng ty TNHH m&#7897;t th&agrave;nh vi&ecirc;n du l&#7883;ch v&agrave; th&#432;&#417;ng m&#7841;i SPT</h2>
                <p>Licensed local travel operator based in Sapa, Lao Cai, Vietnam.</p>
            </aside>
        </div>
    </section>

    <section class="sapa-contact-main">
        <div class="sapa-contact-main-inner">
            <div class="sapa-contact-info-grid">
                <a class="sapa-contact-info-card" href="tel:02143873468">
                    <span><?php echo sapa_contact_icon('phone'); ?></span>
                    <small>Hotline</small>
                    <strong>02143 873468</strong>
                </a>
                <a class="sapa-contact-info-card" href="tel:0919736787">
                    <span><?php echo sapa_contact_icon('phone'); ?></span>
                    <small>Mobile / Zalo</small>
                    <strong>0919 736 787</strong>
                </a>
                <a class="sapa-contact-info-card" href="https://wa.me/84985768359">
                    <span><?php echo sapa_contact_icon('message'); ?></span>
                    <small>WhatsApp</small>
                    <strong>(+84)-985 768 359</strong>
                </a>
                <a class="sapa-contact-info-card" href="mailto:info@sapapathfinder.com">
                    <span><?php echo sapa_contact_icon('mail'); ?></span>
                    <small>Email</small>
                    <strong>info@sapapathfinder.com</strong>
                </a>
                <a class="sapa-contact-info-card" href="https://sapapathfindertravel.com">
                    <span><?php echo sapa_contact_icon('globe'); ?></span>
                    <small>Website</small>
                    <strong>sapapathfindertravel.com</strong>
                </a>
                <div class="sapa-contact-info-card">
                    <span><?php echo sapa_contact_icon('clock'); ?></span>
                    <small>Support</small>
                    <strong>Daily travel assistance</strong>
                </div>
            </div>

            <div class="sapa-contact-panel">
                <div class="sapa-contact-address">
                    <div class="sapa-contact-address-copy">
                        <span><?php echo sapa_contact_icon('map'); ?></span>
                        <div>
                            <small>Office address</small>
                            <h2><?php echo $office_address; ?></h2>
                        </div>
                    </div>
                    <!-- <a class="sapa-contact-map-link" href="https://www.google.com/maps/search/?api=1&query=<?php echo esc_attr($office_map_query); ?>" target="_blank" rel="noopener">
                        Open in Google Maps
                    </a> -->
                    <div class="sapa-contact-map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5338.659161419838!2d103.83970767649954!3d22.336621541575745!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x36cd416b742666b7%3A0x2ac29fa9633d6441!2sSapa%20Pathfinder%20Travel!5e1!3m2!1svi!2s!4v1780973790156!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <div class="sapa-contact-form-shell">
                    <div class="sapa-contact-form-heading">
                        <span>Send a message</span>
                        <h2>Start planning your trip</h2>
                    </div>

                    <?php if (have_posts()) : ?>
                        <?php while (have_posts()) : the_post(); ?>
                            <div class="sapa-contact-content">
                                <?php the_content(); ?>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <div class="sapa-contact-fallback">
                        <a href="mailto:info@sapapathfinder.com">
                            Email our team
                            <?php echo sapa_contact_icon('arrow'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
