{{ partial('macros/group') }}

{% if pager.total_pages > 0 %}
    <div class="group-list clearfix">
        <div class="layui-row layui-col-space20">
            {% for item in pager.items %}
                {% set group_url = url({'for':'home.im_group.show','id':item.id}) %}
                <div class="layui-col-md3">
                    <div class="user-card">
                        <span class="type layui-badge layui-bg-green">{{ type_info(item.type) }}</span>
                        <div class="avatar">
                            <a href="{{ group_url }}" title="{{ item.about }}">
                                <img src="{{ item.avatar }}!avatar_160" alt="{{ item.name }}">
                            </a>
                        </div>
                        <div class="name layui-elip">
                            <a href="{{ group_url }}" title="{{ item.name }}">{{ item.name }}</a>
                        </div>
                        <div class="meta layui-elip">
                            <span>成员：{{ item.user_count }}</span>
                            <span>讨论：{{ item.msg_count }}</span>
                        </div>
                        <div class="action">
                            <span class="layui-btn apply-group" data-id="{{ item.id }}" data-name="{{ item.name }}" data-avatar="{{ item.avatar }}">加入群组</span>
                        </div>
                    </div>
                </div>
            {% endfor %}
        </div>
    </div>
    {{ partial('partials/pager_ajax') }}
{% endif %}