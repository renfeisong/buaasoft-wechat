/**
 * Javascript for Module School Bus Setting Panel
 *
 * @author Zhan Yu
 */
var timeValidation = new RegExp("^(([01]?[0-9])|(2[0-3])):[0-5]?[0-9]$");
var busTable = $('#schoolbus-bus-table').DataTable({
    "paging": false,
    "searching": false,
    "info": false,
    "columnDefs": [{
        "targets": 4,
        "orderable": false,
        "searchable": false
    }]
});
var routeTable = $('#schoolbus-route-table').DataTable({
    "paging": false,
    "searching": false,
    "info": false,
    "columnDefs": [{
        "targets": 2,
        "orderable": false,
        "searchable": false
    }]
});
var routes;
$(".x-editable-text").editable();
$(".x-editable-day").editable({
    source: [{
        id: 0,
        text: '周六、周日'
    }, {
        id: 1,
        text: '周一至周五'
    }],
    select2: {
        placeholder: "选择日期..."
    }
});
$(".x-editable-time").editable({
    validate: function(data) {
        if(!timeValidation.test(data)) {
            return '请输入正确的时间';
        }
        return 0;
    }
});
updateSelection();
$("#schoolbus-route-table").on("change", "#new-route-departure", function() {
    $("#new-route-destination").children('option').remove();
    var type = $(this).val();
    routes[type].forEach(function(d) {
        $("#new-route-destination").append('<option value="'+d+'">'+d+'</option>')
    });
    $("#new-route-destination").parent().find("span.select2-chosen").text(routes[type][0]);
});
$(".delete-item").click(function() {
    var action, table;
    switch($(this).data('name')) {
        case 'bus':
            action = 'delBus';
            table = busTable;
            break;
        case 'route':
            action = 'delRoute';
            table = routeTable;
            break;
    }
    $(this).addClass('transition');
    if ($(this).hasClass('confirm')) {
        // execute
        $(this).removeClass('confirm');
        $(this).addClass('in-progress');
        var _this = this;
        var _table = table;
        $.get('../modules/SchoolBus/ajax.php?action='+action+'&auth='+authKey+'&pk=' + $(this).data('pk'), function(data) {
            console.log(data);
            if(data.status == 0) {
                updateSelection();
                //location.reload(true);
                $("#schoolbus-"+$(_this).data('name')+"-table tr[data-pk='" + $(_this).data('pk') + "']").addClass('to-delete');
                _table.row('.to-delete').remove().draw(false);
            } else alert("服务器去火星玩了。。");
        });
    } else if ($(this).hasClass('idle')) {
        // confirm
        var $this = $(this);
        $this.removeClass('idle');
        $this.addClass('pre-confirm');
        setTimeout(function() {
            $this.removeClass('pre-confirm');
            $this.addClass('confirm');
            setTimeout(function() {
                $this.attr('class', 'button red-button xs-button idle delete-item transition');
            }, 3500)
        }, 200);
    }
});
function updateSelection() {
    $.get('../modules/SchoolBus/ajax.php?action=getRoute', function(data) {
        routes = data.msg;
        try{
            $("#new-route-departure").children('option').remove();
            var keys = [];
            for(var key in routes) {
                keys.push(key);
                var opt = $('<option value="'+key+'">'+key+'</option>');
                $("#new-route-departure").append(opt);
            }
            $("#new-route-departure").parent().find("span.select2-chosen").text(keys[0]);
            $("#new-route-destination").children('option').remove();
            var type = $("#new-route-departure").val();
            routes[type].forEach(function(d) {
                $("#new-route-destination").append('<option value="'+d+'">'+d+'</option>')
            });
            $("#new-route-destination").parent().find("span.select2-chosen").text(routes[type][0]);
        } catch(e) {
            console.log(e);
        }
    });
}
function addBus() {
    var day = $("#new-bus-day").val();
    var departure = $("#new-bus-departure").val();
    var destination = $("#new-bus-destination").val();
    var departureTime = $("#new-bus-departureTime").val();
    if(!timeValidation.test(departureTime)) {
        alert('请正确填写时间');
        return;
    }
    $.post('../modules/SchoolBus/ajax.php?action=newBus&auth='+authKey, {
        day: day,
        departure: departure,
        destination: destination,
        departureTime: departureTime
    }, function(data) {
        if(data.status == 0) {
            updateSelection();
            var newRow = busTable.row.add([
                '<td><a href="#" data-type="select2" data-pk="'+data.msg+'" data-url="'+rootUrl+'/modules/SchoolBus/ajax.php?action=editBus&auth='+authKey+'" data-name="day" class="x-editable-day">'+(day=='1'?'周一至周五':'周六、周日')+'</a></td>',
                '<td><a href="#" data-type="text" data-pk="'+data.msg+'" data-url="'+rootUrl+'/modules/SchoolBus/ajax.php?action=editBus&auth='+authKey+'" data-name="departure" class="x-editable-text">'+departure+'</a></td>',
                '<td><a href="#" data-type="text" data-pk="'+data.msg+'" data-url="'+rootUrl+'/modules/SchoolBus/ajax.php?action=editBus&auth='+authKey+'" data-name="destination" class="x-editable-text">'+destination+'</a></td>',
                '<td><a href="#" data-type="text" data-pk="'+data.msg+'" data-url="'+rootUrl+'/modules/SchoolBus/ajax.php?action=editBus&auth='+authKey+'" data-name="departureTime" class="x-editable-time">'+departureTime+'</a></td>',
                '<td><button class="button red-button xs-button idle delete-item" data-name="bus" data-pk="'+data.msg+'"><span class="idle-only" style="display: none"><i class="fa fa-trash-o"></i> 删除</span><span class="confirm-only" style="display: none">请确认</span><span class="in-progress-only" style="display: none"><i class="fa fa-spinner fa-spin"></i> 稍等..</span></button></td>'
            ]).draw().node();
            $(newRow).find(".x-editable-text").editable();
            $(newRow).find(".x-editable-time").editable({
                validate: function(data) {
                    if(!timeValidation.test(data)) {
                        return '请输入正确的时间';
                    }
                    return 0;
                }
            });
            $(newRow).find(".x-editable-day").editable({
                source: [{
                    id: 0,
                    text: '周六、周日'
                }, {
                    id: 1,
                    text: '周一至周五'
                }],
                select2: {
                    placeholder: "选择日期..."
                }
            });
            $(newRow).find(".delete-item").click(function() {
                $(this).addClass('transition');
                if ($(this).hasClass('confirm')) {
                    // execute
                    $(this).removeClass('confirm');
                    $(this).addClass('in-progress');
                    $.get('../modules/SchoolBus/ajax.php?action=delBus&auth='+authKey+'&pk=' + $(this).data('pk'), function(data) {
                        if(data.status == 0)
                            location.reload(true);
                        else
                            alert("服务器去火星玩了。。");
                    });
                } else if ($(this).hasClass('idle')) {
                    // confirm
                    var $this = $(this);
                    $this.removeClass('idle');
                    $this.addClass('pre-confirm');
                    setTimeout(function() {
                        $this.removeClass('pre-confirm');
                        $this.addClass('confirm');
                        setTimeout(function() {
                            $this.attr('class', 'button red-button xs-button idle delete-item transition');
                        }, 3500)
                    }, 200);
                }
            });
        } else
            alert("服务器去火星玩了。。");
    });
}
function addRoute() {
    var destination = $("#new-route-destination").val();
    var departure = $("#new-route-departure").val();
    $.post('../modules/SchoolBus/ajax.php?action=newRoute&auth='+authKey, {
        destination: destination,
        departure: departure
    }, function(data) {
        if(data.status == 0) {
            updateSelection();
            var newRow = routeTable.row.add([
                departure, destination,
                '<td><button class="button red-button xs-button idle delete-item" data-name="route" data-pk="'+data.msg+'"><span class="idle-only" style="display: none"><i class="fa fa-trash-o"></i> 删除</span><span class="confirm-only" style="display: none">请确认</span><span class="in-progress-only" style="display: none"><i class="fa fa-spinner fa-spin"></i> 稍等..</span></button></td>'
            ]).draw().node();
            $(newRow).find(".delete-item").click(function() {
                $(this).addClass('transition');
                if ($(this).hasClass('confirm')) {
                    // execute
                    $(this).removeClass('confirm');
                    $(this).addClass('in-progress');
                    $.get('../modules/SchoolBus/ajax.php?action=delRoute&auth='+authKey+'&pk=' + $(this).data('pk'), function(data) {
                        if(data.status == 0)
                            location.reload(true);
                        else
                            alert("服务器去火星玩了。。");
                    });
                } else if ($(this).hasClass('idle')) {
                    // confirm
                    var $this = $(this);
                    $this.removeClass('idle');
                    $this.addClass('pre-confirm');
                    setTimeout(function() {
                        $this.removeClass('pre-confirm');
                        $this.addClass('confirm');
                        setTimeout(function() {
                            $this.attr('class', 'button red-button xs-button idle delete-item transition');
                        }, 3500)
                    }, 200);
                }
            });
        }
    });
}