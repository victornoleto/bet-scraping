select
	--q2.*,
	g.home, g.away,
	g.sport,
	--g.category, g.league,
	b1.name as home_bookmaker,
	q2.max_home_odd,
	b2.name as away_bookmaker,
	q2.max_away_odd,
	q2.created_at,
	g.start_at
from (
	select
		q.game_id,
		q.created_at,
		max(q.home_odd) as max_home_odd,
		max(q.away_odd) as max_away_odd
	from (
		select
			o.game_id,
			o.bookmaker_id,
			b.name as bookmaker_name,
			oh.*,
			rank() over(partition by o.game_id, oh.created_at order by oh.home_odd desc, b.id asc) as home_odd_rank,
			rank() over(partition by o.game_id, oh.created_at order by oh.away_odd desc, b.id asc) as away_odd_rank
		from odd_histories as oh
		join odds as o on o.id = oh.odd_id
		join bookmakers as b on b.id = o.bookmaker_id
		where
			oh.draw_odd is null
		order by o.game_id, o.bookmaker_id, oh.created_at
	) q
	where
		q.home_odd_rank = 1 or q.away_odd_rank = 1
	group by
		q.game_id,
		q.created_at
	order by
		q.game_id,
		q.created_at
) q2
join games as g on g.id = q2.game_id
join odd_histories as oh1 on oh1.created_at = q2.created_at and oh1.home_odd = q2.max_home_odd
join odds as o1 on o1.id = oh1.odd_id and o1.game_id = g.id
join bookmakers as b1 on b1.id = o1.bookmaker_id
join odd_histories as oh2 on oh2.created_at = q2.created_at and oh2.away_odd = q2.max_away_odd
join odds as o2 on o2.id = oh2.odd_id and o2.game_id = g.id
join bookmakers as b2 on b2.id = o2.bookmaker_id
where
	q2.max_home_odd > 2 and q2.max_away_odd > 2
order by created_at desc
limit 5