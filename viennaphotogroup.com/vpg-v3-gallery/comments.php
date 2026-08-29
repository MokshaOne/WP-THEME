<?php
/**
 * VPG v3 — Feedback threads.
 * Members-only comments styled as quiet critique, not a comment war.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( post_password_required() ) return;
?>
<section class="g-section g-section--alt g-section--tight" id="feedback">
  <div class="g-wrap" style="max-width:820px">
    <div class="g-head">
      <div>
        <span class="g-kicker"><?php esc_html_e( 'Feedback · members only', 'vpg-v2' ); ?></span>
        <h2 class="g-head__t"><?php
            $count = (int) get_comments_number();
            printf( esc_html( _n( '%d note', '%d notes', $count, 'vpg-v2' ) ), $count );
        ?></h2>
      </div>
    </div>

    <?php if ( have_comments() ) : ?>
      <ol style="list-style:none;margin:0 0 32px;padding:0;display:grid;gap:20px">
        <?php wp_list_comments( [
            'style'       => 'ol',
            'short_ping'  => true,
            'avatar_size' => 40,
            'callback'    => function ( $comment, $args, $depth ) {
                ?>
                <li id="comment-<?php comment_ID(); ?>" style="border:1px solid var(--g-line);padding:16px 20px;margin-left:<?php echo ( $depth - 1 ) * 24; ?>px">
                  <div style="display:flex;gap:12px;align-items:center;margin-bottom:8px">
                    <?php echo get_avatar( $comment, 36, '', '', [ 'style' => 'width:36px;height:36px;object-fit:cover' ] ); ?>
                    <strong style="font-size:14px"><?php echo esc_html( get_comment_author( $comment ) ); ?></strong>
                    <span class="g-meta"><?php echo esc_html( human_time_diff( get_comment_time( 'U' ) ) ); ?></span>
                    <?php if ( $comment->comment_approved === '0' ) : ?><span class="g-meta" style="color:var(--g-red)"><?php esc_html_e( 'awaiting review', 'vpg-v2' ); ?></span><?php endif; ?>
                  </div>
                  <div style="font-size:15px;line-height:1.6;color:var(--g-ink-2)"><?php comment_text(); ?></div>
                <?php
            },
        ] ); ?>
      </ol>
      <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php if ( comments_open() ) : ?>
      <?php comment_form( [
          'title_reply'          => __( 'Leave a note', 'vpg-v2' ),
          'title_reply_before'   => '<p class="g-kicker g-kicker--ink" style="margin-bottom:14px">',
          'title_reply_after'    => '</p>',
          'comment_notes_before' => '<p class="g-form__note">' . esc_html__( 'Honest beats nice. Specific beats vague. Your name stands next to it.', 'vpg-v2' ) . '</p>',
          'comment_field'        => '<div class="g-field"><label for="comment">' . esc_html__( 'Your note', 'vpg-v2' ) . '</label><textarea class="g-textarea" id="comment" name="comment" rows="4" required></textarea></div>',
          'class_submit'         => 'g-btn g-btn--red',
          'label_submit'         => __( 'Post note', 'vpg-v2' ),
          'logged_in_as'         => '',
      ] ); ?>
    <?php elseif ( ! is_user_logged_in() ) : ?>
      <div style="border:1px dashed var(--g-line-2);padding:24px;text-align:center">
        <p class="g-lede" style="margin:0 0 18px;text-align:center"><?php esc_html_e( 'Feedback threads are members-only — honest critique, credited by name.', 'vpg-v2' ); ?></p>
        <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?> →</a>
      </div>
    <?php endif; ?>
  </div>
</section>
