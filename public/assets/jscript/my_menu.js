/**
 * mobileScreen
 * variável que diz se é um dispositivo móvel
 */
const mobileScreen = window.matchMedia("(max-width: 990px )");;

/**
 * Document Ready my_menu
 * Executado no carregamento da página
 */
jQuery(document).ready(function () {
    jQuery('#bt_user').on('click', function() {
        jQuery('#show_user').toggleClass('active');
    });
    jQuery(".content, #titulo").on('click', function(){
        if(jQuery('#show_user').hasClass('active')){
            jQuery('#show_user').toggleClass('active');
            jQuery('.sidebar').toggleClass('active');
        }
    });
    jQuery('.sidebar').hover(function() {
        if(!jQuery('.sidebar').hasClass('active')){
            jQuery('.sidebar').toggleClass('active');
        }
    },function(){
        if(!jQuery('#show_user').hasClass('active')){
            jQuery('.sidebar').toggleClass('active');
        }
    });
    jQuery('.bt-manut').hover(function() {
        jQuery(this).find('.txt-bt-manut').removeClass('d-none');
    },function(){
        jQuery(this).find('.txt-bt-manut').addClass('d-none');
    });
    

    jQuery('.manutencao').hover(function() {
        jQuery('.manut').toggleClass('active');
        if(jQuery('#show_user').hasClass('active')){
            jQuery('#show_user').toggleClass('active');
        }
    },function(){
        jQuery('.manut').toggleClass('active');
    });
});;

/**
 * btn_menu clique
 * Evento Clique do botão de menu
 */
jQuery('.btn_menu').click(function(){
    jQuery(this).toggleClass("click");
    jQuery('.sidebar').toggleClass("show");
});;
    
/**
 * sidebar clique
 * Evento Clique na Barra de Menus
 */
jQuery('.sidebar ul li a').click(function(){
    var id = jQuery(this).attr('id');
    jQuery('nav ul li ul.item-show-'+id).toggleClass("show");
    jQuery('nav ul li #'+id+' span').toggleClass("rotate");
});;

/**
 * nav ul li clique
 * Evento Clique na Opção do Menu
 */
jQuery('nav ul li').click(function(){
    jQuery(this).addClass("active").siblings().removeClass("active");
});;