
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

 $('#bt_cronGenerator').on('click',function(){
    jeedom.getCronSelectModal({},function (result) {
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=refreshCron]').value(result.value);
    });
});


$(function() {
    $("#table_cmd tbody").delegate(".listCmdmyfoxv2", 'click', function(event) {
        $('.description').hide();
        $('.version').hide();
        $('.required').hide();
        $('.description.' + $('#sel_addPreConfigCmdmyfoxv2').value()).show();
        $('.version.' + $('#sel_addPreConfigCmdmyfoxv2').value()).show();
        $('.required.' + $('#sel_addPreConfigCmdmyfoxv2').value()).show();
        $('#md_addPreConfigCmdmyfoxv2').modal('show');
        $('#bt_addPreConfigCmdmyfoxv2Save').undelegate().unbind();
        var tr = $(this).closest('tr');
        $("#div_mainContainer").delegate("#bt_addPreConfigCmdmyfoxv2Save", 'click', function(event) {
            var cmd_data = json_decode($('.json_cmd.' + $('#sel_addPreConfigCmdmyfoxv2').value()).value());
            tr.setValues(cmd_data, '.cmdAttr');
            $('#md_addPreConfigCmdmyfoxv2').modal('hide');
        });
    });

    $("#sel_addPreConfigCmdmyfoxv2").on('change', function(event) {
        $('.description').hide();
        $('.version').hide();
        $('.required').hide();
        $('.description.' + $(this).value()).show();
        $('.version.' + $(this).value()).show();
        $('.required.' + $(this).value()).show();
    });

    $("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
});

 function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
	tr += '<td>';
	tr += '<span class="cmdAttr" data-l1key="id"></span>';
	tr += '</td>';
	
    tr += '<td>';
	tr += '<div class="col-sm-2">';
tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icône</a>';
tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
tr += '</div>';
    
	
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 160px;" placeholder="{{Nom}}"></td>';
    tr += '<td style="width: 30px;">';
    if(!isset(_cmd.type) || _cmd.type == 'info' ){
        tr += '<span> <label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-size="mini" data-label-text="{{Historiser}}" data-l1key="isHistorized" />{{Historiser}}</label></span>';
    }
    tr += '</td>';
	tr += '<td style="width: 30px;">';
	tr += '<span> <label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" data-size="mini" data-label-text="{{Afficher}}" checked/>{{Afficher}}</label></span>';
	tr += '</td>';
    tr += '<td style="width: 60px;">';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
	tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}