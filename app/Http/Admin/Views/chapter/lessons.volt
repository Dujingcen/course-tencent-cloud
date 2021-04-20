{% extends 'templates/main.volt' %}

{% block content %}

    {% set back_url = url({'for':'admin.course.chapters','id':course.id}) %}
    {% set add_chapter_url = url({'for':'admin.chapter.add'},{'type':'chapter','course_id':course.id}) %}
    {% set add_lesson_url = url({'for':'admin.chapter.add'},{'type':'lesson','course_id':course.id,'parent_id':chapter.id}) %}

    <div class="kg-nav">
        <div class="kg-nav-left">
            <span class="layui-breadcrumb">
                <a class="kg-back" href="{{ back_url }}"><i class="layui-icon layui-icon-return"></i>返回</a>
                <a><cite>{{ course.title }}</cite></a>
                <a><cite>{{ chapter.title }}</cite></a>
                <a><cite>课时管理</cite></a>
            </span>
        </div>
        <div class="kg-nav-right">
            <a class="layui-btn layui-btn-sm" href="{{ add_chapter_url }}"><i class="layui-icon layui-icon-add-1"></i>添加章</a>
            <a class="layui-btn layui-btn-sm" href="{{ add_lesson_url }}"><i class="layui-icon layui-icon-add-1"></i>添加课</a>
        </div>
    </div>

    {% if course.model == 1 %}
        {{ partial('chapter/lessons_vod') }}
    {% elseif course.model == 2 %}
        {{ partial('chapter/lessons_live') }}
    {% elseif course.model == 3 %}
        {{ partial('chapter/lessons_read') }}
    {% elseif course.model == 4 %}
        {{ partial('chapter/lessons_offline') }}
    {% endif %}

{% endblock %}

{% block inline_js %}

    <script>

        layui.use(['jquery', 'layer', 'form'], function () {

            var $ = layui.jquery;
            var layer = layui.layer;
            var form = layui.form;

            form.on('switch(free)', function (data) {
                var checked = $(this).is(':checked');
                var free = checked ? 1 : 0;
                var url = $(this).data('url');
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {free: free},
                    success: function (res) {
                        layer.msg(res.msg, {icon: 1});
                    },
                    error: function (xhr) {
                        var json = JSON.parse(xhr.responseText);
                        layer.msg(json.msg, {icon: 2});
                        data.elem.checked = !checked;
                        form.render();
                    }
                });
            });

            $('.kg-comment').on('click', function () {
                var url = $(this).data('url');
                layer.open({
                    type: 2,
                    title: '评论管理',
                    area: ['1000px', '600px'],
                    content: url
                });
            });

        });

    </script>

{% endblock %}