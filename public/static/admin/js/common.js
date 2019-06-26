layui.config({
	base: '../../static/admin/js/module/'
}).extend({
	dialog: 'dialog',
});

layui.use(['form', 'jquery', 'laydate', 'layer', 'laypage', 'dialog',   'element'], function() {
	var form = layui.form,
		layer = layui.layer,
		$ = layui.$,
		dialog = layui.dialog;
	//获取当前iframe的name值
	var iframeObj = $(window.frameElement).attr('name');
	// console.dir($(window.frameElement));
	// console.log(iframeObj);
	//全选
	form.on('checkbox(allChoose)', function(data) {
		var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
		child.each(function(index, item) {
			item.checked = data.elem.checked;
		});
		form.render('checkbox');
	});
	//渲染表单
	form.render();	
	//顶部添加
	$('.addBtn').click(function() {

		var title = $(this).attr('data-title');
		var url = $(this).attr('data-url');
		var pw = $(this).attr('data-w');
		var ph = $(this).attr('data-h');
		if(!pw){ 
			pw = '700px';
		}
		if(!ph){ 
			ph = '620px';
		}
		//将iframeObj传递给父级窗口
		parent.page(title, url, iframeObj, pw, ph);
		return false;

	})
	// .mouseenter(function() {

	// 	dialog.tips('添加', '.addBtn');

	// })
	//顶部排序
	$('.listOrderBtn').click(function() {
		var url=$(this).attr('data-url');
		dialog.confirm({
			message:'您确定要进行排序吗？',
			success:function(){
				layer.msg('确定了')
			},
			cancel:function(){
				layer.msg('取消了')
			}
		})
		return false;

	})
	// .mouseenter(function() {

	// 	dialog.tips('批量排序', '.listOrderBtn');

	// })

	//tips按钮提示
	$(document).on('mouseenter', '.btn_tips', function() {
		var title = $(this).attr('data-title');
		if(title){ 
			dialog.tips(title, $(this));
		}
		
	})

	//顶部批量删除
	$('.delBtn').click(function() {
		var url=$(this).attr('data-url');
		dialog.confirm({
			message:'您确定要删除选中项',
			success:function(){
				var ids = '';
				$('.layui-table').find('tbody').find('.layui-form-checked').siblings('input').each(function(){ 
					ids += $(this).data('id') + ',';
				});
				//layer.msg('删除了'+ ids);
				delAjax(url, {ids: ids});
			},
			cancel:function(){
				layer.msg('取消了')
			}
		})
		return false;
	})

    //顶部批量执行
    $('.setBtn').click(function() {
        var url=$(this).attr('data-url');
        dialog.confirm({
            message:'您确定要执行选中项',
            success:function(){
                var ids = '';
                $('.layui-table').find('tbody').find('.layui-form-checked').siblings('input').each(function(){
                    ids += $(this).data('id') + ',';
                });
                //layer.msg('删除了'+ ids);
                delAjax(url, {ids: ids});
            },
            cancel:function(){
                layer.msg('取消')
            }
        })
        return false;

    })
	// .mouseenter(function() {

	// 	dialog.tips('批量删除', '.delBtn');

	// })	
	//列表添加
	$('#table-list').on('click', '.add-btn', function() {
		var title = $(this).attr('data-title');
		var url = $(this).attr('data-url');
		var pw = $(this).attr('data-w');
		var ph = $(this).attr('data-h');
		if(!pw){ 
			pw = '700px';
		}
		if(!ph){ 
			ph = '620px';
		}
		//将iframeObj传递给父级窗口
		parent.page(title, url, iframeObj, pw, ph);
		return false;
	})
	//列表删除
	$('#table-list').on('click', '.del-btn', function() {
		var url=$(this).attr('data-url');
		var id = $(this).attr('data-id');
		dialog.confirm({
			message:'您确定要进行删除吗？',
			success:function(){
				delAjax(url, {ids: id});
			},
			cancel:function(){
				layer.msg('取消了')
			}
		})
		return false;
	})

	$('#shoop').on('click', '.del-btn', function() {
		var url=$(this).attr('data-url');
		var id = $(this).attr('data-id');
		dialog.confirm({
			message:'您确定要进行删除吗？',
			success:function(){
				delAjax(url, {ids: id});
			},
			cancel:function(){
				layer.msg('取消了')
			}
		})
		return false;
	})
	//列表跳转
	$('#table-list,.tool-btn').on('click', '.go-btn', function() {
		var url=$(this).attr('data-url');
		var id = $(this).attr('data-id');
		window.location.href=url+"?id="+id;
		return false;
	})

	//提示后请求
	$('#table-list').on('click', '.ts-btn', function() {
		var url = $(this).data('url');
		var title = $(this).data('title');
		if(title == ''){
			title = '确认操作吗？'
		}
		dialog.confirm({
			message: title,
			success:function(){
				loading = layer.load(2, {
					shade: [0.2, '#000']
				});
				$.get(url, function(data){ 
					layer.close(loading);
					console.dir(data);
					if(data.error_code){ 
						layer.msg(data.msg, {icon: 2, shade: 0.1, time: 1000}, function(){ 
		
						});
						return false;
					} 
					layer.msg(data.msg, {icon: 1, shade: 0.1, time: 1000}, function () {
						refresh();
					});
				});
			},
			cancel:function(){
				layer.msg('取消了')
			}
		})
		return false;
	})

	//编辑栏目
	$('#table-list').on('click', '.edit-btn', function() {
		var That = $(this);
		var title = That.attr('data-title');
		var id = That.attr('data-id');
		var url=That.attr('data-url');
		var pw=That.attr('data-w');
		var ph=That.attr('data-h');
		if(!pw){ 
			pw = '700px';
		}
		if(!ph){ 
			ph = '620px';
		}
		//将iframeObj传递给父级窗口
		parent.page(title, url + "?id=" + id, iframeObj, pw, ph);
		return false;
	})

	//删除请求
	function delAjax(url, data){ 
		loading = layer.load(2, {
            shade: [0.2, '#000']
        });
		$.get(url, data, function(data){ 
			layer.close(loading);
        	console.dir(data);
        	if(data.error_code){ 
                layer.msg(data.msg, {icon: 2, shade: 0.1, time: 1000}, function(){ 

                });
                return false;
            } 
        	layer.msg(data.msg, {icon: 1, shade: 0.1, time: 1000}, function () {
                refresh();
            });
		});
	}

	$('#table-list').on('click', '.tab_btn', function() {
		var id = $(this).data('tab');
		var title = $(this).data('title');
		var url = $(this).data('url');
		var isActive = $('.main-layout-tab .layui-tab-title', parent.document).find("li[lay-id=" + id + "]");
		console.log(isActive.length);
		if(isActive.length > 0) {
			//切换到选项卡
			parent.element.tabChange('tab', id);
			$('#iframe'+id, parent.document).attr('src', url);
		} else {
			parent.element.tabAdd('tab', {
				title: title,
				content: '<iframe src="' + url + '" name="iframe' + id + '" class="iframe" framborder="0" id="iframe' + id + '" data-id="' + id + '" scrolling="auto" width="100%"  height="100%"></iframe>',
				id: id
			});
			parent.element.tabChange('tab', id);
		}
		$('#main-layout', parent.document).removeClass('hide-side');
	});

});

/**
 * 控制iframe窗口的刷新操作
 */
var iframeObjName;

//父级弹出页面
function page(title, url, obj, w, h) {
	if(title == null || title == '') {
		title = false;
	};
	if(url == null || url == '') {
		url = "404.html";
	};
	if(w == null || w == '') {
		w = '700px';
	};
	if(h == null || h == '') {
		h = '350px';
	};
	iframeObjName = obj;
	//如果手机端，全屏显示
	if(window.innerWidth <= 768) {
		var index = layer.open({
			type: 2,
			title: title,
			area: [320, h],
			fixed: false, //不固定
			content: url
		});
		layer.full(index);
	} else {
		var index = layer.open({
			type: 2,
			title: title,
			area: [w, h],
			fixed: false, //不固定
			content: url
		});
	}
}

/**
 * 刷新子页,关闭弹窗
 */
function refresh() {
	//根据传递的name值，获取子iframe窗口，执行刷新
	if(window.frames[iframeObjName]) {
		window.frames[iframeObjName].location.reload();

	} else {
		window.location.reload();
	}

	layer.closeAll();
}