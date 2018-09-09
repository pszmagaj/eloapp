<?php

class MigrateCtrl {
    function __construct($db) {
        $this->db = $db;
    }

    private function insertGamesFormUser($games) {
        $gamesValues1 = [];
        foreach($games as $game) {
            $gamesValues1[] = "(" . join(", ", $game) . ")";
        }

        $gamesValues = join(", ", $gamesValues1);
        $sql = '
        INSERT IGNORE INTO games (winner_user_nid, looser_user_nid, winner_rating_before, looser_rating_before, rating_diff, cdate) VALUES '.$gamesValues;

        //echo $sql;
        $this->db->query($sql);
    }

    private function migrateOneUser($userNid) {
        $sql = '
        SELECT rh1.user_nid as user_nid1, rh2.user_nid as user_nid2, rh1.rating as rating1, rh2.rating as rating2, rh1.cdate
        FROM ratings_history rh1
        LEFT JOIN ratings_history rh2 ON (
            DATE_ADD(rh1.cdate, INTERVAL 1 SECOND) >= rh2.cdate AND DATE_SUB(rh1.cdate, INTERVAL 1 SECOND) <= rh2.cdate
        	AND rh1.user_nid != rh2.user_nid
        )
        WHERE rh1.user_nid = '.$userNid.'
        AND rh2.user_nid IS NOT NULL
        ORDER BY rh1.cdate ASC
        ';
        $records = $this->db
            ->query($sql)
            ->fetchAll();

        $prevRecord = null;    
        $games = [];
        foreach($records as $record) {
            $prevRating1 = isset($prevRecord) ? $prevRecord['rating1'] : 1500;
            $rating_diff = $record['rating1'] - $prevRating1;
            $prevRating2 = $record['rating2'] + $rating_diff;
            $winner_user_nid = $rating_diff >= 0 ? $record['user_nid1'] : $record['user_nid2'];
            $looser_user_nid = $rating_diff >= 0 ? $record['user_nid2'] : $record['user_nid1'];

            $winner_rating_before = $rating_diff >= 0 ? $prevRating1 : $prevRating2;
            $looser_rating_before = $rating_diff >= 0 ? $prevRating2 : $prevRating1;

            $games[] = [
                'winner_user_nid' => $winner_user_nid,
                'looser_user_nid' => $looser_user_nid,
                'winner_rating_before' => $winner_rating_before,
                'looser_rating_before' => $looser_rating_before,
                'rating_diff' => abs($rating_diff),
                'cdate' => "'" . $record['cdate'] . "'"
            ];

            $prevRecord = $record;
        }

        $this->insertGamesFormUser($games);
        // echo '<pre>'.print_r($games, true) .'</pre>';
    }

    function migrate() {
        $sql = '
        SELECT * FROM users
        ';
        $users = $this->db
            ->query($sql)
            ->fetchAll();

        foreach($users as $user) {
            echo $user['user_nid'].'<br />';
            $this->migrateOneUser($user['user_nid']);
        }
    }
}