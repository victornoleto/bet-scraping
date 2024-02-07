select
	g.id as game_id, g.server_id,
	g.sport_id, g.category, g.league,
	g.ht, g.at, g.match_time,
	bm.name as betting_market, q2.period, q2.alternative,
	q2.o1, b1.name as o1_bookmaker_name,
	q2.o2, b2.name as o2_bookmaker_name,
	(((q2.o1*q2.o2)/(q2.o1+q2.o2)) - 1) * 100 as profit_percentage,
	q2.refreshed_at
from (
	select
		game_id,
		refreshed_at,
		betting_market_id,
		period,
		alternative,
		max(o1) as o1,
		max(o2) as o2,
		(array_agg(bookmaker_id))[1] as o1_bookmaker_id,
		coalesce((array_agg(bookmaker_id))[2], (array_agg(bookmaker_id))[1]) as o2_bookmaker_id
	from (
		select
			game_id, bookmaker_id, betting_market_id,
			period, alternative,
			o1, o2, refreshed_at,
			rank() over(partition by game_id, betting_market_id, period, alternative, refreshed_at order by o1 desc, bookmaker_id) as o1_rank,
			rank() over(partition by game_id, betting_market_id, period, alternative, refreshed_at order by o2 desc, bookmaker_id) as o2_rank
		from odds
	) q
	where
		q.o1_rank = 1 or q.o2_rank = 1
	group by
		game_id,
		refreshed_at,
		betting_market_id,
		period,
		alternative
) q2
join games as g on g.id = q2.game_id
join betting_markets as bm on bm.id = q2.betting_market_id
join bookmakers as b1 on b1.id = q2.o1_bookmaker_id
join bookmakers as b2 on b2.id = q2.o2_bookmaker_id
where
	q2.o1 >= 2 and q2.o2 >= 2