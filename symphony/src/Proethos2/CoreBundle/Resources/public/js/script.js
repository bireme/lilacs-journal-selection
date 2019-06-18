
// This file is part of the ProEthos Software. 
// 
// Copyright 2013, PAHO. All rights reserved. You can redistribute it and/or modify
// ProEthos under the terms of the ProEthos License as published by PAHO, which
// restricts commercial use of the Software. 
// 
// ProEthos is distributed in the hope that it will be useful, but WITHOUT ANY
// WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
// PARTICULAR PURPOSE. See the ProEthos License for more details. 
// 
// You should have received a copy of the ProEthos License along with the ProEthos
// Software. If not, see
// https://github.com/bireme/proethos2/blob/master/LICENSE.txt


$(function(){
        
    // masks
    $('.mask-money').mask('00000000000000000000000000000000000.00', {reverse: true});

    // initters
    $('[data-toggle="tooltip"]').tooltip()
/*
    $(".btnLeft").click(function () {
        var selectedItem = $(this).closest('div.multiple').find(".rightValues option:selected");
        $(this).closest('div.multiple').find(".leftValues").append(selectedItem);
    });

    $(".btnRight").click(function () {
        var selectedItem = $(this).closest('div.multiple').find(".leftValues option:selected");
        $(this).closest('div.multiple').find(".rightValues").append(selectedItem);
    });

    $('.submission-button-controls').click(function() {
        $('#select-publication-type option, #select-language option').prop('selected', true);
    });
*/
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        // increaseArea: '20%'
    });

});