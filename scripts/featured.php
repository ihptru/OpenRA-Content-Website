<?PHP
date_default_timezone_set('UTC');

if ( php_sapi_name() != "cli" )
    exit(1);

include("../settings.php");
include("../db_mysql.php");

db::connect();

$query = "
    -- most favourited
    SELECT
	table_name,
	table_id,
	'peoples' as type
    FROM fav_item
	WHERE table_name <> 'articles'
	GROUP BY table_name,table_id
	HAVING (COUNT(table_id) = 
		    (SELECT MAX(user_id_amount) FROM
			(SELECT COUNT(user_id) AS user_id_amount FROM fav_item GROUP BY table_id)
			AS amounts
		    )
	       )
    UNION ALL
    -- editors choice, etc from featured table (type in table must be: editors)
    SELECT
	table_name,
	table_id,
	type
    FROM (SELECT
	    table_name,
	    table_id,
	    type
	 FROM featured ORDER BY RAND() LIMIT 1) as editors
    UNION ALL
    -- featured ( get all items for last month, find most viewed unit,map,guide,replay(4 items in result), among those 4 most viewed items, find most liked one by users )
    SELECT
	table_name,
	table_id,
	'featured' AS type
    FROM (SELECT
	    table_name,
	    table_id
	  FROM    
	    (
		SELECT
		    table_name,
		    table_id,
		    MAX(viewed) AS viewed,
		    (SELECT COUNT(*) FROM fav_item WHERE table_id = max_viewed.table_id and table_name = 'units') AS liked
		FROM (SELECT
			'units' AS table_name,
			uid AS table_id,
			viewed
		      FROM units WHERE TIMESTAMPDIFF(DAY, posted, CURRENT_TIMESTAMP) < 30
		    ) AS max_viewed
		UNION ALL
		SELECT
		    table_name,
		    table_id,
		    MAX(viewed) AS viewed,
		    (SELECT COUNT(*) FROM fav_item WHERE table_id = max_viewed.table_id and table_name = 'maps') AS liked
		FROM (SELECT
			'maps' AS table_name,
			uid AS table_id,
			viewed
		      FROM maps WHERE TIMESTAMPDIFF(DAY, posted, CURRENT_TIMESTAMP) < 30
		    ) AS max_viewed
		UNION ALL
		SELECT
		    table_name,
		    table_id,
		    MAX(viewed) AS viewed,
		    (SELECT COUNT(*) FROM fav_item WHERE table_id = max_viewed.table_id and table_name = 'guides') AS liked
		FROM (SELECT
			'guides' AS table_name,
			uid AS table_id,
			viewed
		      FROM guides WHERE TIMESTAMPDIFF(DAY, posted, CURRENT_TIMESTAMP) < 30
		    ) AS max_viewed
		UNION ALL
		SELECT
		    table_name,
		    table_id,
		    MAX(viewed) AS viewed,
		    (SELECT COUNT(*) FROM fav_item WHERE table_id = max_viewed.table_id and table_name = 'replays') AS liked
		FROM (SELECT
			'replays' AS table_name,
			uid AS table_id,
			viewed
		      FROM replays WHERE TIMESTAMPDIFF(DAY, posted, CURRENT_TIMESTAMP) < 30
		    ) AS max_viewed
	    ) AS last_items WHERE table_id <> 0 GROUP BY table_id HAVING (MAX(liked)) ORDER BY RAND() LIMIT 1
	) AS result_table
    UNION ALL
    -- most discussed
    SELECT
	table_name,
	table_id,
	'discussed' as type
    FROM comments
	WHERE table_name <> 'articles'
	GROUP BY table_name,table_id
	HAVING (COUNT(table_id) = 
		    (SELECT MAX(user_id_amount) FROM
			(SELECT COUNT(user_id) AS user_id_amount FROM comments GROUP BY table_id)
			AS amounts
		    )
	       )
    UNION ALL
    -- new map
    SELECT
	'maps' AS table_name,
	uid AS table_id,
	'new_map' AS type
    FROM (SELECT * FROM maps ORDER BY posted DESC LIMIT 1) AS tmaps
    UNION ALL
    -- new guide
    SELECT
	'guides' AS table_name,
	uid AS table_id,
	'new_guide' AS type
    FROM (SELECT * FROM guides ORDER BY posted DESC LIMIT 1) AS tguides
    UNION ALL
    -- new unit
    SELECT
	'units' AS table_name,
	uid AS table_id,
	'new_unit' AS type
    FROM (SELECT * FROM units ORDER BY posted DESC LIMIT 1) AS tunits
    UNION ALL
    -- new replay
    SELECT
	'replays' AS table_name,
	uid AS table_id,
	'new_replay' AS type
    FROM (SELECT * FROM replays ORDER BY posted DESC LIMIT 1) AS treplays
    UNION ALL
    -- most viewed
    SELECT
	table_name,
	uid AS table_id,
	'viewed' AS type
    FROM (   
	SELECT
	    uid,
	    'maps' AS table_name,
	    viewed
	FROM maps WHERE viewed = (SELECT MAX(viewed) FROM maps ORDER BY RAND() LIMIT 1)
	UNION
	SELECT
	    uid,
	    'units' AS table_name,
	    viewed
	FROM units WHERE viewed = (SELECT MAX(viewed) FROM units ORDER BY RAND() LIMIT 1)
	UNION
	SELECT
	    uid,
	    'guides' AS table_name,
	    viewed
	FROM guides WHERE viewed = (SELECT MAX(viewed) FROM guides ORDER BY RAND() LIMIT 1)
	UNION
	SELECT
	    uid,
	    'replays' AS table_name,
	    viewed
	FROM replays WHERE viewed = (SELECT MAX(viewed) FROM replays ORDER BY RAND() LIMIT 1)

	ORDER BY viewed DESC LIMIT 1
    ) AS tablename
	
    ORDER BY RAND() LIMIT 1
";
$res = db::executeQuery($query);
$row = db::nextRowFromQuery($res);
$data = $row['table_name'].",".$row['table_id'].",".$row['type'];

db::disconnect();

$fp = fopen(dirname(__FILE__)."/../featured.temp", "w");
fwrite($fp, $data . "\n");
fclose($fp);

?>
