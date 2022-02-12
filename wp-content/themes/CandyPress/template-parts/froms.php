<?php

	if(count($_POST) != 0){ // on top of page
		// Then collect form data and try to send the form and write messages and so on...
		// Note: set $mailer->SMTPDebug to 0 to prevent it to write server messages on the page
      echo "error";
	}
?>

<style>

    #contact input[type="text"],
    #contact input[type="email"],
    #contact input[type="tel"],
    #contact input[type="url"],
    #contact textarea,
    #contact button[type="submit"] {
        font: 400 12px/16px "Roboto", Helvetica, Arial, sans-serif;
    }

    #contact {
        background: #F9F9F9;
        padding: 25px;
        margin: 150px 0;
        box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
    }

    fieldset {
        border: medium none !important;
        margin: 0 0 10px;
        min-width: 100%;
        padding: 0;
        width: 100%;
    }

    #contact input[type="text"],
    #contact input[type="email"],
    #contact input[type="tel"],
    #contact input[type="url"],
    #contact textarea {
        width: 100%;
        border: 1px solid #ccc;
        background: #FFF;
        margin: 0 0 5px;
        padding: 10px;
    }

    #contact input[type="text"]:hover,
    #contact input[type="email"]:hover,
    #contact input[type="tel"]:hover,
    #contact input[type="url"]:hover,
    #contact textarea:hover {
        -webkit-transition: border-color 0.3s ease-in-out;
        -moz-transition: border-color 0.3s ease-in-out;
        transition: border-color 0.3s ease-in-out;
        border: 1px solid #aaa;
    }

    #contact textarea {
        height: 100px;
        max-width: 100%;
        resize: none;
    }

    #contact button[type="submit"] {
        cursor: pointer;
        width: 100%;
        border: none;
        background: #4CAF50;
        color: #FFF;
        margin: 0 0 5px;
        padding: 10px;
        font-size: 15px;
    }

    #contact button[type="submit"]:hover {
        background: #43A047;
        -webkit-transition: background 0.3s ease-in-out;
        -moz-transition: background 0.3s ease-in-out;
        transition: background-color 0.3s ease-in-out;
    }

    #contact button[type="submit"]:active {
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.5);
    }

    #contact input:focus,
    #contact textarea:focus {
        outline: 0;
        border: 1px solid #aaa;
    }

    ::-webkit-input-placeholder {
        color: #888;
    }

    :-moz-placeholder {
        color: #888;
    }

    ::-moz-placeholder {
        color: #888;
    }

    :-ms-input-placeholder {
        color: #888;
    }
</style>



<div class="container" style="width:400px; margin: auto;">
	<form id="contact">
		<h4>Contact us for custom quote</h4>
		<fieldset>
			<input placeholder="Your name" name="Name" type="text" tabindex="1" autofocus>
		</fieldset>
		<fieldset>
			<input placeholder="Your Email Address" name="Eamil" type="email" tabindex="2">
		</fieldset>
		<fieldset>
			<textarea name="message" placeholder="Type your message here...." tabindex="5"></textarea>
		</fieldset>
		<fieldset>
			<button type="submit" id="contact-submit">Submit</button>
		</fieldset>
	</form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

    (function ($){
        $('#contact').submit(function (event){
            event.preventDefault();
            var endpoint = '<?php echo admin_url('admin-ajax.php'); ?>';
            var form = $('#contact').serialize();
            var formdata = new FormData();
            formdata.append('action','contact');
            formdata.append('nonce', '<?php echo wp_create_nonce('ajax-nonce'); ?>')
            formdata.append('contact',form);
            $.ajax(endpoint, {
                type:'POST',
                data:formdata,
                processData:false,
                contentType: false,
                success:function(res){
                    alert(res.data);
                    $('#contact').trigger('reset');
                },
                error:function (err) {
                    alert(err.responseJSON.data);
                },
            })
        })
    })(jQuery)

</script>



