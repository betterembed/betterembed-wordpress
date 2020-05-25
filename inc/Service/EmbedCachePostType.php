<?php


namespace BetterEmbed\WordPress\Service;


use BetterEmbed\WordPress\Plugin;

class EmbedCachePostType implements Service {

	/** @var Plugin */
	protected $plugin;

	public function init(Plugin $plugin){
		$this->plugin = $plugin;
		$this->registerPostType();

		add_action( 'before_delete_post', array( $this, 'deleteAttachmentsWithPost') );

		/**
		 * TODO: Consider skipping the trash to immediately delete this post type?
		 * But since this post type is private and will probably only ever get deleted by this plugin an alternative
		 * is to just handle this by always using `wp_delete_attachment()` with the `$force_delete` parameter.
		 */

		/**
		 * TODO: Consider hiding attachments for this post type from the Media Library.
		 */
	}

	protected function registerPostType(){

		/**
		 * Enables UI für the Cache Post Type for debugging.
		 *
		 * @since 0.0.1-alpha
		 *
		 * @param int $enabled If the UI is enabled. Default `false`.
		 */
		$showUi = (bool) apply_filters(
			$this->plugin->namespace('showUI'),
			( defined('BETTEREMBED_DEBUG') && BETTEREMBED_DEBUG )
		);

		register_post_type(
			$this->postTypeKey(),
			array(
				'labels'           => array(
					'name'          => __( 'BetterEmbeds', 'betterembed' ),
					'singular_name' => __( 'BetterEmbed',  'betterembed' ),
				),
				'description'      => __( 'Cache for Embeds', 'betterembed' ),
				'public'           => false,
				'hierarchical'     => false,
				'show_ui'          => $showUi,
				'menu_icon'        => 'dashicons-editor-code',
				'rewrite'          => false,
				'query_var'        => false,
				'delete_with_user' => false,
				'can_export'       => false,
				'supports'         => array(),
			)
		);

	}

	/**
	 * Before deleting a post delete all associated attachments.
	 *
	 * @param int $postId
	 */
	public function deleteAttachmentsWithPost( int $postId ){

		if( get_post_type($postId) !== $this->postTypeKey() ) return;

		$attachments = get_attached_media( '', $postId );

		foreach ($attachments as $attachment) {
			//TODO: Handle error?
			wp_delete_attachment( $attachment->ID, 'true' );
		}

	}

	protected function postTypeKey(){
		return $this->plugin->prefix('cache');
	}

}
