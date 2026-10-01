-- Nobody is handed a design job by the system.
--
-- Until now, a new job with no designer named could be given to whoever had the fewest
-- open jobs. That is wrong for this shop: the board is shared, a designer takes a job off
-- it himself, and the work is counted to whoever actually accepted it. A name that
-- appeared on its own made it look as though someone had taken a job they had never seen.
--
-- The round-robin code is gone from the application. The setting goes with it, so an old
-- value cannot sit in the table looking as if it still means something.
DELETE FROM `settings` WHERE `setting_key` = 'auto_assign_designer';
