<?php
/**
 * Helper Functions
 *
 * @package    AMP Base\Comments
 * @since       1.0.0
 */

// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
if( !class_exists( 'AMP_Base_Comments' ) ) {
	 /**
     * AMP_Base_Comments class
     * @since  1.0.0
     */
	class AMP_Base_Comments {
		
		/**
		* Static function hooks
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function hooks() {
			// Compatibility with WP Comments
			add_action('admin_post_amp_base_comment', array(__CLASS__, 'amp_base_comment'));
			add_action('admin_post_nopriv_amp_base_comment', array(__CLASS__, 'amp_base_comment'));
		}
		public static function comment_form( $args = array(), $post_id = null ) {
			if ( null === $post_id )
				$post_id = get_the_ID();

			// Exit the function when comments for the post are closed.
			if ( ! comments_open( $post_id ) ) {
				/**
				 * Fires after the comment form if comments are closed.
				 *
				 * @since 3.0.0
				 */
				do_action( 'comment_form_comments_closed' );

				return;
			}

			$commenter = wp_get_current_commenter();
			$user = wp_get_current_user();
			$user_identity = $user->exists() ? $user->display_name : '';

			$args = wp_parse_args( $args );
			if ( ! isset( $args['format'] ) )
				$args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';

			$req      = get_option( 'require_name_email' );
			$aria_req = ( $req ? " aria-required='true'" : '' );
			$html_req = ( $req ? " required='required'" : '' );
			$html5    = 'html5' === $args['format'];
			$fields   =  array(
				'author' => '<p class="comment-form-author">' . '<label for="author">' . esc_html__( 'Name', 'amp-accelerated-mobile-pages' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
				            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" maxlength="245"' . $aria_req . $html_req . ' /></p>',
				'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'amp-accelerated-mobile-pages' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
				            '<input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" maxlength="100" aria-describedby="email-notes"' . $aria_req . $html_req  . ' /></p>',
				'url'    => '<p class="comment-form-url"><label for="url">' . esc_html__( 'Website', 'amp-accelerated-mobile-pages' ) . '</label> ' .
				            '<input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" maxlength="200" /></p>',
			);

			$required_text = sprintf( ' ' .
			/* translators: %s: Required input asteric  */
			 esc_attr__('Required fields are marked %s', 'amp-accelerated-mobile-pages'), '<span class="required">*</span>' );

			/**
			 * Filters the default comment form fields.
			 *
			 * @since 3.0.0
			 *
			 * @param array $fields The default comment fields.
			 */
			$fields = apply_filters( 'comment_form_default_fields', $fields );
			$defaults = array(
				'fields'               => $fields,
				'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun', 'amp-accelerated-mobile-pages' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" aria-required="true" required="required"></textarea></p>',
				/** This filter is documented in wp-includes/link-template.php */
				'must_log_in'          => '<p class="must-log-in">' . sprintf(
				                              /* translators: %s: login URL */
				                              __( 'You must be <a href="%s">logged in</a> to post a comment.', 'amp-accelerated-mobile-pages' ),
				                              wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) )
				                          ) . '</p>',
				/** This filter is documented in wp-includes/link-template.php */
				'logged_in_as'         => '<p class="logged-in-as">' . sprintf(
				                              /* translators: 1: edit user link, 2: accessibility text, 3: user name, 4: logout URL */
				                              __( '<a href="%1$s" aria-label="%2$s">Logged in as %3$s</a>. <a href="%4$s">Log out?</a>', 'amp-accelerated-mobile-pages' ),
				                              get_edit_user_link(),
				                              /* translators: %s: user name */
				                              esc_attr( sprintf( __( 'Logged in as %s. Edit your profile.', 'amp-accelerated-mobile-pages' ), $user_identity ) ),
				                              $user_identity,
				                              wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) )
				                          ) . '</p>',
				'comment_notes_before' => '<p class="comment-notes"><span id="email-notes">' . __( 'Your email address will not be published.', 'amp-accelerated-mobile-pages' ) . '</span>'. ( $req ? $required_text : '' ) . '</p>',
				'comment_notes_after'  => '',
				'action'               => admin_url('admin-post.php'),
				'id_form'              => 'commentform',
				'id_submit'            => 'submit',
				'class_form'           => 'comment-form',
				'class_submit'         => 'submit',
				'name_submit'          => 'submit',
				'title_reply'          => __( 'Leave a Reply', 'amp-accelerated-mobile-pages' ),
				 /* translators: %s: Author name */
				'title_reply_to'       => __( 'Leave a Reply to %s', 'amp-accelerated-mobile-pages' ),
				'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
				'title_reply_after'    => '</h3>',
				'cancel_reply_before'  => ' <small>',
				'cancel_reply_after'   => '</small>',
				'cancel_reply_link'    => __( 'Cancel reply', 'amp-accelerated-mobile-pages' ),
				'label_submit'         => __( 'Post Comment', 'amp-accelerated-mobile-pages' ),
				'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
				'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
				'format'               => 'xhtml',
			);

			/**
			 * Filters the comment form default arguments.
			 *
			 * Use {@see 'comment_form_default_fields'} to filter the comment fields.
			 *
			 * @since 3.0.0
			 *
			 * @param array $defaults The default comment form arguments.
			 */
			$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );

			// Ensure that the filtered args contain all required default values.
			$args = array_merge( $defaults, $args );

			/**
			 * Fires before the comment form.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_before' );
			?>
			<div id="respond" class="comment-respond">
				<?php
				echo $args['title_reply_before'];

				comment_form_title( $args['title_reply'], $args['title_reply_to'] );

				//echo $args['cancel_reply_before'];

				//cancel_comment_reply_link( $args['cancel_reply_link'] );

				//echo $args['cancel_reply_after'];

				echo $args['title_reply_after'];

				if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) :
					echo $args['must_log_in'];
					/**
					 * Fires after the HTML-formatted 'must log in after' message in the comment form.
					 *
					 * @since 3.0.0
					 */
					do_action( 'comment_form_must_log_in_after' );
				else : ?>
					<form action-xhr="<?php echo esc_url( $args['action'] ); ?>" method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="<?php echo esc_attr( $args['class_form'] ); ?>"<?php echo $html5 ? ' novalidate' : ''; ?> target="_top">
						<input type="hidden" name="action" value="amp_base_comment"/>

						<?php
						/**
						 * Fires at the top of the comment form, inside the form tag.
						 *
						 * @since 3.0.0
						 */
						do_action( 'comment_form_top' );

						if ( is_user_logged_in() ) :
							/**
							 * Filters the 'logged in' message for the comment form for display.
							 *
							 * @since 3.0.0
							 *
							 * @param string $args_logged_in The logged-in-as HTML-formatted message.
							 * @param array  $commenter      An array containing the comment author's
							 *                               username, email, and URL.
							 * @param string $user_identity  If the commenter is a registered user,
							 *                               the display name, blank otherwise.
							 */
							echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity );

							/**
							 * Fires after the is_user_logged_in() check in the comment form.
							 *
							 * @since 3.0.0
							 *
							 * @param array  $commenter     An array containing the comment author's
							 *                              username, email, and URL.
							 * @param string $user_identity If the commenter is a registered user,
							 *                              the display name, blank otherwise.
							 */
							do_action( 'comment_form_logged_in_after', $commenter, $user_identity );

						else :

							echo $args['comment_notes_before'];

						endif;

						// Prepare an array of all fields, including the textarea
						$comment_fields = array( 'comment' => $args['comment_field'] ) + (array) $args['fields'];

						/**
						 * Filters the comment form fields, including the textarea.
						 *
						 * @since 4.4.0
						 *
						 * @param array $comment_fields The comment fields.
						 */
						$comment_fields = apply_filters( 'comment_form_fields', $comment_fields );

						// Get an array of field names, excluding the textarea
						$comment_field_keys = array_diff( array_keys( $comment_fields ), array( 'comment' ) );

						// Get the first and the last field name, excluding the textarea
						$first_field = reset( $comment_field_keys );
						$last_field  = end( $comment_field_keys );

						foreach ( $comment_fields as $name => $field ) {

							if ( 'comment' === $name ) {

								/**
								 * Filters the content of the comment textarea field for display.
								 *
								 * @since 3.0.0
								 *
								 * @param string $args_comment_field The content of the comment textarea field.
								 */
								echo apply_filters( 'comment_form_field_comment', $field );

								echo $args['comment_notes_after'];

							} elseif ( ! is_user_logged_in() ) {

								if ( $first_field === $name ) {
									/**
									 * Fires before the comment fields in the comment form, excluding the textarea.
									 *
									 * @since 3.0.0
									 */
									do_action( 'comment_form_before_fields' );
								}

								/**
								 * Filters a comment form field for display.
								 *
								 * The dynamic portion of the filter hook, `$name`, refers to the name
								 * of the comment form field. Such as 'author', 'email', or 'url'.
								 *
								 * @since 3.0.0
								 *
								 * @param string $field The HTML-formatted output of the comment form field.
								 */
								echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";

								if ( $last_field === $name ) {
									/**
									 * Fires after the comment fields in the comment form, excluding the textarea.
									 *
									 * @since 3.0.0
									 */
									do_action( 'comment_form_after_fields' );
								}
							}
						}

						$submit_button = sprintf(
							$args['submit_button'],
							esc_attr( $args['name_submit'] ),
							esc_attr( $args['id_submit'] ),
							esc_attr( $args['class_submit'] ),
							esc_attr( $args['label_submit'] )
						);

						/**
						 * Filters the submit button for the comment form to display.
						 *
						 * @since 4.2.0
						 *
						 * @param string $submit_button HTML markup for the submit button.
						 * @param array  $args          Arguments passed to `comment_form()`.
						 */
						$submit_button = apply_filters( 'comment_form_submit_button', $submit_button, $args );

						$submit_field = sprintf(
							$args['submit_field'],
							$submit_button,
							get_comment_id_fields( $post_id )
						);

						/**
						 * Filters the submit field for the comment form to display.
						 *
						 * The submit field includes the submit button, hidden fields for the
						 * comment form, and any wrapper markup.
						 *
						 * @since 4.2.0
						 *
						 * @param string $submit_field HTML markup for the submit field.
						 * @param array  $args         Arguments passed to comment_form().
						 */
						echo apply_filters( 'comment_form_submit_field', $submit_field, $args );

						/**
						 * Fires at the bottom of the comment form, inside the closing </form> tag.
						 *
						 * @since 1.5.0
						 *
						 * @param int $post_id The post ID.
						 */
						do_action( 'comment_form', $post_id );
						?>
						<div submit-success>
						    <template type="amp-mustache">
						      <?php esc_html_e('Success! Your comment has been added.', 'amp-accelerated-mobile-pages' );?>
						    </template>
						</div>
						<div submit-error>
					    	<template type="amp-mustache">
					      		{{message}} 
					    	</template>
				 		</div>
					</form>
					
				<?php endif; ?>
			</div><!-- #respond -->
			<?php

			/**
			 * Fires after the comment form.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_after' );
		}

		/**
		* Static function amp_base_comment
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function amp_base_comment() {
			header('AMP-Access-Control-Allow-Source-Origin:'.site_url());
			header('Access-Control-Expose-Headers: AMP-Redirect-To');
			$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
			if ( is_wp_error( $comment ) ) {
				$data = intval( $comment->get_error_data() );
				if ( ! empty( $data ) ) {
					status_header(500);
					$json_response = array();
					$json_response['message'] = $comment->get_error_message();
					die(json_encode($json_response));
				} else {
					exit;
				}
			}

			$user = wp_get_current_user();
			do_action( 'set_comment_cookies', $comment, $user );
			$location = empty( $_POST['redirect_to'] ) ? get_comment_link( $comment ) : $_POST['redirect_to'] . '#comment-' . $comment->comment_ID;
			$location = apply_filters( 'comment_post_redirect', $location, $comment );
			header('AMP-Redirect-To:'.$location);
			die('{"sucess": "true"}');

		}

		/**
		* Retrieve HTML content for reply to comment link.
		* @since 1.0.0
	 	* @param array $args {
	 	*     Optional. Override default arguments.
	 	*
	 	*     @type string $add_below  The first part of the selector used to identify the comment to respond below.
	 	*                              The resulting value is passed as the first parameter to addComment.moveForm(),
	 	*                              concatenated as $add_below-$comment->comment_ID. Default 'comment'.
		*     @type string $respond_id The selector identifying the responding comment. Passed as the third parameter
	 	*                              to addComment.moveForm(), and appended to the link URL as a hash value.
	 	*                              Default 'respond'.
	 	*     @type string $reply_text The text of the Reply link. Default 'Reply'.
	 	*     @type string $login_text The text of the link to reply if logged out. Default 'Log in to Reply'.
	 	*     @type int    $max_depth  The max depth of the comment tree. Default 0.
		*     @type int    $depth      The depth of the new comment. Must be greater than 0 and less than the value
		*                              of the 'thread_comments_depth' option set in Settings > Discussion. Default 0.
	 	*     @type string $before     The text or HTML to add before the reply link. Default empty.
	 	*     @type string $after      The text or HTML to add after the reply link. Default empty.
	 	* }
	 	* @param int|WP_Comment $comment Comment being replied to. Default current comment.
 		* @param int|WP_Post    $post    Post ID or WP_Post object the comment is going to be displayed on.
	 	*                                Default current post.
	 	* @return void|false|string Link to show comment form, if successful. False, if comments are closed.
		*/
		public static function get_comment_reply_link( $args = array(), $comment = null, $post = null ) {
			$defaults = array(
				'add_below'     => 'comment',
				'respond_id'    => 'respond',
				'reply_text'    => esc_html__( 'Reply', 'amp-accelerated-mobile-pages' ),
				/* translators: Comment reply button text. 1: Comment author name */
				'reply_to_text' => esc_html__( 'Reply to %s', 'amp-accelerated-mobile-pages' ),
				'login_text'    => esc_html__( 'Log in to Reply', 'amp-accelerated-mobile-pages' ),
				'max_depth'     => 0,
				'depth'         => 0,
				'before'        => '',
				'after'         => ''
			);

			$args = wp_parse_args( $args, $defaults );

			if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] ) {
				return;
			}

			$comment = get_comment( $comment );

			if ( empty( $post ) ) {
				$post = $comment->comment_post_ID;
			}

			$post = get_post( $post );

			if ( ! comments_open( $post->ID ) ) {
				return false;
			}

			/**
			 * Filters the comment reply link arguments.
			 *
			 * @since 4.1.0
			 *
			 * @param array      $args    Comment reply link arguments. See get_comment_reply_link()
			 *                            for more information on accepted arguments.
			 * @param WP_Comment $comment The object of the comment being replied to.
			 * @param WP_Post    $post    The WP_Post object.
			 */
			$args = apply_filters( 'comment_reply_link_args', $args, $comment, $post );

			if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
				$link = sprintf( '<a rel="nofollow" class="comment-reply-login" href="%s">%s</a>',
					esc_url( wp_login_url( get_permalink() ) ),
					$args['login_text']
				);
			} else {
				

				$link = sprintf( "<a rel='nofollow' class='comment-reply-link' href='%s'  aria-label='%s'>%s</a>",
					esc_url( add_query_arg( 'replytocom', $comment->comment_ID, get_permalink( $post->ID ) ) ) . "#" . $args['respond_id'],
					esc_attr( sprintf( $args['reply_to_text'], $comment->comment_author ) ),
					$args['reply_text']
				);
			}

			/**
			 * Filters the comment reply link.
			 *
			 * @since 2.7.0
			 *
			 * @param string  $link    The HTML markup for the comment reply link.
			 * @param array   $args    An array of arguments overriding the defaults.
			 * @param object  $comment The object of the comment being replied.
			 * @param WP_Post $post    The WP_Post object.
			 */
			return apply_filters( 'comment_reply_link', $args['before'] . $link . $args['after'], $args, $comment, $post );
		}
			
	}
}
AMP_Base_Comments::hooks();