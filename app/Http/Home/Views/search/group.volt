{% if pager.total_pages > 0 %}
    <div class="search-group-list">
        {% for item in pager.items %}
            {% set group_url = url({'for':'home.im_group.show','id':item.id}) %}
            {% set owner_url = url({'for':'home.user.show','id':item.owner.id}) %}
            {% set item.about = item.about|default('这个家伙真懒，什么也没有留下！') %}
            <div class="search-group-card">
                <div class="avatar">
                    <a href="{{ group_url }}" target="_blank">
                        <img src="{{ item.avatar }}!avatar_160" alt="{{ item.name }}">
                    </a>
                </div>
                <div class="info">
                    <div class="name">
                        <a href="{{ group_url }}" target="_blank">{{ item.name }}</a>
                    </div>
                    <div class="about">{{ item.about }}</div>
                    <div class="meta">
                        <span>组长：<a href="{{ owner_url }}" target="_blank">{{ item.owner.name }}</a></span>
                        <span>成员：{{ item.user_count }}</span>
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>
{% else %}
    {{ partial('search/empty') }}
{% endif %}