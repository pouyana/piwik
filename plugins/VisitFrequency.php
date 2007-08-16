<?php
	
class Piwik_Plugin_VisitFrequency extends Piwik_Plugin
{	
	public function __construct()
	{
	}

	public function getInformation()
	{
		$info = array(
			'name' => 'VisitorFrequency',
			'description' => 'VisitorFrequency',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function install()
	{
	}
	
	function uninstall()
	{
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
		);
		return $hooks;
	}
	
	function archiveDay($notification)
	{
		$this->ArchiveProcessing = $notification->getNotificationObject();
		
		$query = "SELECT 	count(distinct visitor_idcookie) as nb_uniq_visitors_returning,
							count(*) as nb_visits_returning, 
							sum(visit_total_actions) as nb_actions_returning,
							max(visit_total_actions) as max_actions_returning, 
							sum(visit_total_time) as sum_visit_length_returning,							
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count_returning
				 	FROM ".$this->ArchiveProcessing->logTable."
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 		AND visitor_returning = 1
				 	GROUP BY visitor_returning";
		$row = $this->ArchiveProcessing->db->fetchRow($query, array( $this->ArchiveProcessing->strDateStart, $this->ArchiveProcessing->idsite ) );
		
		if($row==false)
		{
			$row['nb_visits_returning'] = 0;
			$row['nb_actions_returning'] = 0;
			$row['max_actions_returning'] = 0;
			$row['sum_visit_length_returning'] = 0;
			$row['bounce_count_returning'] = 0;
		}
		
		foreach($row as $name => $value)
		{
			$record = new Piwik_Archive_Processing_Record_Numeric($name, $value);
		}
		
	}
}