/**
 * Created by J Rodrigo on 15/04/2016.
 */
function showOrderDown(id_order) {

    if(id_order == undefined)
        return false;

    $('.details').not('#details-'+id_order).hide();
    $('#details-'+id_order).toggle();

};