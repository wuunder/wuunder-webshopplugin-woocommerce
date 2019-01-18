<style>
    .warning {
        text-align: center;
        padding-top: 30px;
    }

    .btn-primary {
        background: #8dcc00 !important;
        border-color: #8dcc00 !important;
        -webkit-box-shadow: 0 1px 0 #8dcc00 !important;
        box-shadow: 0 1px 0 #8dcc00 !important;
        color: #fff;
        text-decoration: none;
        text-shadow: 0 -1px 1px #8dcc00, 1px 0 1px #8dcc00, 0 1px 1px #8dcc00, -1px 0 1px #8dcc00 !important;
    }

    a.btn-primary:hover {
        background: #94d600 !important;
        border-color: #94d600 !important;
        -webkit-box-shadow: 0 1px 0 #94d600 !important;
        box-shadow: 0 1px 0 #94d600 !important;
        color: #fff;
        text-decoration: none;
        text-shadow: 0 -1px 1px #94d600, 1px 0 1px #94d600, 0 1px 1px #94d600, -1px 0 1px #94d600 !important;
    }

    .submit-wuunder {
        margin-top: 30px;
    }

    p {
        font-size: 11px;
    }
</style>
<script>

    function check_phone() {
        var phonenumber = jQuery( '#wuunderPhonenumber' ).val();
        var pattern = new RegExp( /^\+[1-9]{1}[0-9]{10}$/ );
        if ( pattern.test( phonenumber ) ) {
            jQuery( '#wuunderPhonenumber' ).parent().removeClass( 'has-error' ).addClass( 'has-success' );
        } else {
            jQuery( '#wuunderPhonenumber' ).parent().removeClass( 'has-success' ).addClass( 'has-error' );
        }
    }
    jQuery( '#wuunderPhonenumber' ).keyup( function () {
        check_phone();
    } );

    jQuery( document ).ready( function () {
        check_phone( );

        jQuery( '.company-address' ).hide();
        jQuery( '.company-address.address-0' ).show();

        jQuery( '.pickup-address' ).click( function () {
            jQuery( '.company-address' ).hide();
            jQuery( '.company-address.address-' + jQuery( this ).val() ).show();
        } );

        jQuery( '.button-wuunder' ).click( function () {
            jQuery( ".button-wuunder" ).attr( "disabled", true );
//            jQuery( '.page-form' ).submit();
            jQuery.post( window.location.href, jQuery( '.page-form' ).serialize(), function ( data ) {
                console.log( JSON.parse( data ) );
                var postData = JSON.parse( data );
                if ( postData.errors.length ) {
                    var html = "<ul>";
                    for ( var error in postData.errors ) {
                        html += "<li>" + postData.errors[error].field + ": " + postData.errors[error].messages.join( ", " ) + "</li>";
                    }
                    jQuery( "#alertBox .alert" ).html( html + "</ul>" );
                    jQuery( "#alertBox" ).show();
                    jQuery( ".button-wuunder" ).attr( "disabled", false );
                } else if ( postData.success ) {
                    window.location.replace( postData.redirect_url );
                }
            } );
        } );
        TB_HEIGHT = Math.round( jQuery( window ).height() - 20 );
        jQuery( window ).resize( function () {
            TB_HEIGHT = Math.round( jQuery( window ).height() - 20 );
            console.log( jQuery( window ).height() )
        } );
    } );
</script>

<?php
if ( empty( $check_company ) ) { ?>
    <div class="warning">
        <img src="<?php echo Woocommerce_Wuunder::$plugin_url . 'assets/images/wuunder_logo.png' ?>"
             style="padding:20px 30px 20px 20px; width:180px;">
        <h2>Uw instellingen zijn niet volledig ingevuld</h2>
        <p>Controleer de gegevens van uw <b>"Standaard afhaaladres"</b>.</p>
        <a class="btn btn-primary" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=wuunder' ); ?>">Controleer
            uw instellingen</a>
    </div>
    <?php
    exit;
}
?>

<form method="post" class="page-form">
    <?php if ( 'retour' == $_GET['label'] ) { ?>
        <h1><img style="width:34px;"
                 src="<?php echo dirname( plugin_dir_url( __FILE__ ) ) ?>/assets/images/create-icon.png"> Retourlabel
            aanmaken</h1>
    <?php } else { ?>
        <h1><img style="width:34px;"
                 src="<?php echo dirname( plugin_dir_url( __FILE__ ) ) ?>/assets/images/create-icon.png"> Verzendlabel
            aanmaken</h1>
    <?php } ?>
    <?php
    $c = true;
    foreach ( $data as $row ) : 
        ?>
        <fieldset>
            <input type="hidden" name="data[customer_reference]" value="<?php echo $row['orderid']; ?>">
            <input type="hidden" name="data[value]" value="<?php echo ( 0 === $row['waarde'] || empty( $row['waarde'] ) ) ? 25 : $row['waarde']; ?>">
            <input type="hidden" name="data[picture]" value="<?php echo $row['picture']; 
            ?>
            ">
            <div id="alertBox" class="row">
                <div class="col-xs-12">
                    <div class="alert alert-danger">

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label style="line-height: 28px; margin-bottom:0;">Ontvanger</label>
                                </div>
                                <div class="col-sm-8">
                                    <?php if ( 'retour' == $_GET['label'] ) { ?>
                                        <select class="pickup-address form-control" id="inputType"
                                                name="data[pickup_address]">
                                            <?php foreach ( $available_addresses as $pickup_address => $straat ) { ?>
                                                <option
                                                    value="<?php echo $pickup_address; ?>"><?php echo $straat; ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <?php if ( 'retour' == $_GET['label'] ) { ?>
                                <?php
                                foreach ( $available_addresses as $pickup_address => $straat ) {
                                    echo '<div class="company-address address-' . $pickup_address . '>';
                                    if ( 0 == $pickup_address ) {
                                        // Get Woocommerce Wuunder Settings
                                        echo get_option( 'wc_wuunder_company_name' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_firstname' ) . ' ' . get_option( 'wc_wuunder_company_lastname' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_street' ) . ' ' . get_option( 'wc_wuunder_company_housenumber' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_postode' ) . ' ' . get_option( 'wc_wuunder_company_city' ) . ' ' . get_option( 'wc_wuunder_company_country' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_email' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_phone' ) . '<br>';

                                    } else {
                                        echo get_option( 'wc_wuunder_company_name_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_firstname_' . $pickup_address ) . ' ' . get_option( 'wc_wuunder_company_lastname_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_street_' . $pickup_address ) . ' ' . get_option( 'wc_wuunder_company_housenumber_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_postode_' . $pickup_address ) . ' ' . get_option( 'wc_wuunder_company_city_' . $pickup_address ) . ' ' . get_option( 'wc_wuunder_company_country_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_email_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_phone_' . $pickup_address ) . '<br>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            <?php } else { ?>
                                <?php if ( 'NL' == $row['landcode']  && ( empty( $row['straat'] ) || empty( $row['huisnummer'] ) ) ) { ?>
                                    <span style="color:red">Deze order bevat geen geldige straatnaam- en huisnummergegevens, en kan daarom niet worden ge-exporteerd! Waarschijnlijk is deze order geplaatst voordat de Wuunder plugin werd geactiveerd. De gegevens kunnen wel handmatig worden ingevoerd in het order scherm.</span>
                                <?php } else { ?>
                                    <?php echo $row['bedrijfsnaam'] ?><br/>
                                    t.n.v. <?php echo $row['firstname'] ?><?php echo $row['lastname'] ?><br/>
                                    <?php echo $row['straat'] ?><?php echo $row['huisnummer'] . $row['huisnummertoevoeging'] ?>
                                    <br/>
                                    <?php echo $row['postcode'] ?><?php echo $row['woonplaats'] ?><?php echo $row['landcode']; ?>
                                    <br/>
                                    <?php echo $row['email'] ?><br/>
                                    <?php echo $row['telefoon'] ?><br/>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label style="line-height: 28px; margin-bottom:0;">Verzender</label>
                                </div>
                                <div class="col-sm-8">
                                    <?php if ( empty( $_GET['label'] ) ) { ?>
                                        <select class="pickup-address form-control" id="inputType"
                                                name="data[pickup_address]">
                                            <?php foreach ( $available_addresses as $pickup_address => $straat ) { ?>
                                                <option
                                                    value="<?php echo $pickup_address; ?>"><?php echo $straat; ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="panel-body">
                            <?php if ( 'retour' ==  $_GET['label'] ) { ?>
                                <?php echo $row['bedrijfsnaam'] ?><br/>
                                t.n.v. <?php echo $row['firstname'] ?><?php echo $row['lastname'] ?><br/>
                                <?php echo $row['straat'] ?><?php echo $row['huisnummer'] . $row['huisnummertoevoeging'] ?>
                                <br/>
                                <?php echo $row['postcode'] ?><?php echo $row['woonplaats'] ?><?php echo $row['landcode']; ?>
                                <br/>
                                <?php echo $row['email'] ?><br/>
                                <?php echo $row['telefoon'] ?><br/>
                            <?php } else { ?>
                                <?php
                                foreach ( $available_addresses as $pickup_address => $straat ) {
                                    echo '<div class="company-address address-' . $pickup_address . '">';
                                    if (  0 == $pickup_address ) {
                                        // Get Woocommerce Wuunder Settings
                                        echo get_option( 'wc_wuunder_company_name' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_firstname' ) . ' ' . get_option( 'wc_wuunder_company_lastname' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_street' ) . ' ' . get_option( 'wc_wuunder_company_housenumber' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_postode' ) . ' ' . get_option( 'wc_wuunder_company_city' ) . ' ' . get_option( 'wc_wuunder_company_country' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_email' ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_phone' ) . '<br>';

                                    } else {
                                        echo get_option( 'wc_wuunder_company_name_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_firstname_' . $pickup_address ) . ' ' . get_option( 'wc_wuunder_company_lastname_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_street_' . $pickup_address ) . ' ' . get_option( 'wc_wuunder_company_housenumber_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_postode_' . $pickup_address ) . ' ' . get_option( 'wc_wuunder_company_city_' . $pickup_address ) . ' ' . get_option( 'wc_wuunder_company_country_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_email_' . $pickup_address ) . '<br>';
                                        echo get_option( 'wc_wuunder_company_phone_' . $pickup_address ) . '<br>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Controle mobiele nummer klant</div>
                <div class="panel-body">
                    <div class="form-group has-success">
                        <label class="control-label" for="wuunderPhonenumber">Mobiele nummer <span class="text-primary">*</span>
                            ( Gebruik de opmaak: +31612345678 )</label>
                        <input type="text" class="form-control input-sm" name="data[phone_number]"
                               id="wuunderPhonenumber"
                               value="<?php echo( 0 === ( empty( $row['telefoon'] ) || strcmp( $row['telefoon'], "+31" ) ) ? "" : $row['telefoon'] ); ?>"
                               required="">
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Bestelling</div>
                <div class="panel-body">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th width="30">#</th>
                            <th>Product</th>
                            <th width="100">Gewicht</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $total_weight = 0;
                        foreach ( $row['bestelling'] as $product ) {
                            $total_weight += $product['total_weight'];
                            ?>
                            <tr>
                                <td><?php echo $product['quantity'] . 'x'; ?></td>
                                <td><?php echo $product['name'] . $product['variation']; ?></td>
                                <td><?php echo $product['total_weight']; ?></td>
                            </tr>
                            <?php
                        }
                        if ( 0 === $total_weight ) {
                            $total_weight = 5000;
                        }
                        ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td>&nbsp;</td>
                            <td>Totaal gewicht</td>
                            <td><?php echo $total_weight; ?>gr</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Verzendlabel aanmaken voor #<?php echo $row['ordernr']; ?></div>
                <div class="panel-body">

                    <div class="form-group">
                        <label for="inputType" class="control-label">Soort verpakking <span
                                class="text-primary">*</span></label>
                        <select class="form-control input-sm" id="inputType" name="data[kind]">
                            <option value="package" selected="">Pakket</option>
                            <option value="document">Document</option>
                            <option value="pallet"<?php if ( $total_weight > 23000 ) {
                                echo ' selected';
                            } ?>>Pallet
                            </option>
                        </select>
                    </div>
                    <?php
                    if ( 0 === strcmp( $product['dimensions'], "Niet beschikbaar" ) || empty( $product['dimensions'] ) ) {
                        $size = array( 40, 30, 25 );
                    } else {
                        $size = explode( ' x ', $product['dimensions'] );
                    }
                    ?>
                    <div class="form-group">
                        <label class="control-label" for="wuunderLength">Afmetingen ( l x b x h ) <span
                                class="text-primary">*</span></label>
                        <div class="row">
                            <div class="col-xs-4">
                                <div class="input-group">
                                    <input class="form-control input-sm" name="data[length]" id="wuunderLength"
                                           type="number" min="1" value="<?php echo $size[0] ?>" required="">
                                    <div class="input-group-addon input-sm">cm</div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="input-group">
                                    <input class="form-control input-sm" name="data[width]" id="wuunderWidth"
                                           type="number" min="1" value="<?php echo $size[1]; ?>" required="">
                                    <div class="input-group-addon input-sm">cm</div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="input-group">
                                    <input class="form-control input-sm" name="data[height]" id="wuunderHeight"
                                           type="number" min="1" value="<?php echo str_replace( ' cm', '', $size[2] ); ?>"
                                           required="">
                                    <div class="input-group-addon input-sm">cm</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="wuunderWeight">Gewicht <span
                                class="text-primary">*</span></label>
                        <div class="input-group">
                            <input class="form-control input-sm" name="data[weight]" id="wuunderWeight" type="number"
                                   value="<?php echo $total_weight; ?>" required="">
                            <div class="input-group-addon input-sm">gram</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="wuunderReference">Inhoud zending <span
                                class="text-primary">*</span></label>
                        <input class="form-control input-sm" name="data[description]" id="wuunderReference" type="text"
                               placeholder="Wat is de inhoud van de zending die je gaat versturen?"
                               value="<?php foreach ( $row['bestelling'] as $product ) {
                                   echo $product['name'] . $product['variation'];
                                   if ( next( $row['bestelling'] ) ) {
                                       echo ', ';
                                   }
                               } ?>" required="">
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="wuunderMessage">Chat bericht</label>
                        <textarea class="form-control input-sm" name="data[personal_message]" id="wuunderMessage"
                                  placeholder="Stuur een persoonlijk bericht naar de ontvanger"></textarea>
                    </div>

                    <div class="form-group">
                        <?php if ( 'retour' == $_GET['label'] ) { ?>
                            <input type="hidden" name="action" value="wcwuunder-retour">
                            <input type="submit" value="Vraag retourlabel aan"
                                   class="button-wuunder btn save_order btn-primary tips">
                        <?php } else { ?>
                            <input type="hidden" name="action" value="wcwuunder-export">
                            <input type="submit" value="Vraag verzendlabel aan"
                                   class="button-wuunder btn save_order btn-primary tips">
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div id="error-message" class="alert alert-dismissible alert-danger" style="display: none"></div>

        </fieldset>
    <?php endforeach; ?>

</form>
