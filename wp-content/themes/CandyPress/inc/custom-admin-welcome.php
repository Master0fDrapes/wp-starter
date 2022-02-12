<?php
add_action( 'admin_footer', 'rv_custom_dashboard_widget' );
function rv_custom_dashboard_widget() {
	if ( get_current_screen()->base !== 'dashboard' ) {
		return;
	}
	?>

	<div id="custom-id" class="welcome-panel" style="display: none; overflow: hidden;">
		<div class="welcome-panel-content" style="min-height: 300px; overflow: hidden;">
			<h2 style="padding: 40px;">Welcome! to backend of your site</h2>
			<p class="about-description"></p>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column"><a href="#">Documentation</a></div>
				<div class="welcome-panel-column"></div>
				<div class="welcome-panel-column welcome-panel-last"></div>
			</div>
		</div>
	</div>

	<script>
		jQuery(document).ready(function($) {
			$('#welcome-panel').after($('#custom-id').show());
		});
	</script>
<?php }

add_action('admin_head', 'admin_custom_css');

function admin_custom_css() {
  echo '<style>
    .welcome-panel::before {
	    background:none;
    } 
    .welcome-panel h2{
        font-size: 24px;
        font-weight: 400;
    }
    .welcome-panel .welcome-panel-column-container{
        background: transparent;
        color:#fff;
    }
    .welcome-panel a{
        text-decoration: none;
        color:#fff;
    }
    .welcome-panel a:hover{
        color:#e3e3e3;
    }
  </style>';
}