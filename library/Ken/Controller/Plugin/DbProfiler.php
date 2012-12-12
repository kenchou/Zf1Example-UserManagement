<?php
class Ken_Controller_Plugin_DbProfiler extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopShutdown()
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        //[EN] Get DB Adaptor
        $profiler = $db->getProfiler ();
        $totalTime = $profiler->getTotalElapsedSecs ();
        $queryCount = $profiler->getTotalNumQueries ();
        $longestTime = 0;
        $longestQuery = null;

        if ($queryCount) {
            foreach ($profiler->getQueryProfiles() as $query) {
                if ($query->getElapsedSecs() > $longestTime) {
                    $longestTime  = $query->getElapsedSecs();
                    $longestQuery = $query->getQuery();
                }
            }
        }

        echo 'Executed ', $queryCount, ' queries in ', $totalTime, ' seconds', "\n";
        echo 'Average query length: ', $queryCount ? $totalTime / $queryCount : 'n/a', ' seconds', "\n";
        echo 'Queries per second: ', $totalTime ? $queryCount / $totalTime : 'n/a', "\n";
        echo 'Longest query length: ', $longestTime, "\n";
        echo "Longest query: \n", $longestQuery, "\n";
    }
}
