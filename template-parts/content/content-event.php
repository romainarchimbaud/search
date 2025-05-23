<?php

/**
 * Template part for displaying event.
 *
 * @package kindabreak
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly.

?>

<?php

$get_start_date = get_field('start_date', get_the_ID());
$get_end_date = get_field('end_date', get_the_ID());
$terms = wp_get_post_terms(get_the_ID(), 'location');
$start_date_day = date_i18n("d", strtotime($get_start_date));
$start_date_month = date_i18n("M", strtotime($get_start_date));
$end_date_day = date_i18n("d", strtotime($get_end_date));
$end_date_month = date_i18n("M", strtotime($get_end_date));

?>

<article class="event-item col-4 <?php echo (is_front_page()) ? 'swiper-slide' : ''; ?>">
    <div class="event-item__inner has-overlay position-relative p-2 p-sm-3 mt-5">
        <header class="position-absolute top-0 start-0 w-100 z-3 d-flex align-items-start justify-content-between">
            <div class="event-item__city d-inline-flex align-items-center px-2 py-1 bg-primary">
                <span class="kbicon kbicon-nearby fw-normal fs-6 me-1"></span>
                <?php if (!empty($terms) && isset($terms[0])) : ?>
                    <span class="kbf-bold text-uppercase text-nowrap fs-6"><?php echo $terms[0]->name; ?></span>
                <?php endif; ?>
            </div>
            <div class="event-item__date me-2 me-sm-4 mt-n4 text-primary">
                <?php if ($get_start_date != $get_end_date) { ?>
                    <time datetime="<?php echo esc_attr($get_start_date); ?>" class="d-inline-flex flex-column justify-content-center align-items-center rounded-circle position-relative bg-white">
                        <span class="event-item__day"><?php echo esc_html($start_date_day); ?></span>
                        <span class="event-item__month"><?php echo esc_html($start_date_month); ?></span>
                    </time>
                <?php } ?>
                <time datetime="<?php echo esc_attr($get_end_date); ?>" class="d-inline-flex flex-column justify-content-center align-items-center rounded-circle position-relative bg-secondary">
                    <span class="event-item__day">&gt;<?php echo esc_html($end_date_day); ?></span>
                    <span class="event-item__month"><?php echo esc_html($end_date_month); ?></span>
                </time>
            </div>
        </header>
        <div class="event-item__image h-auto position-relative overflow-hidden has-overlay__img">
            <div class="event-item__image--container">
                <?php
                if (has_post_thumbnail()) {
                    echo get_the_post_thumbnail(
                        get_the_ID(),
                        'medium_large',
                        array(
                            'alt' => get_the_title(),
                            'class' => 'object-fit-cover position-absolute w-100 h-100 top-0 start-0 end-0 bottom-0'
                        )
                    );
                }
                ?>
            </div>
        </div>
        <h2 class="event-item__title kb-subtitle text-center text-truncate m-0 pt-3 lh-1">
            <a href="<?php the_permalink(); ?>" class="stretched-link link-hover-green"><?php the_title(); ?></a>
        </h2>
    </div>
</article>
