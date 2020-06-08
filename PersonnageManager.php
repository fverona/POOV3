<?php


class PersonnageManager
{

	private $_db; // inqtance de PDO

	
	public function __construct($db)
	{
		$this->_db = $db;
	}


	public function add(Personnage $perso)
	{

		$q = $this->_db->prepare('INSERT INTO personnages_v3(nom, type) VALUES(:nom, :type)');

	    $q->bindValue(':nom', $perso->nom());
	    $q->bindValue(':type', $perso->type());

	    $q->execute();

	    $perso->hydrate([
	      'id' => $this->_db->lastInsertId(),
	      'degats' => 0,
	      'atout' => 0
    	]);
	}


	public function count()
	{
	 	$q = $this->_db->prepare('select count(*) from personnages_v3');

		$q->execute();

		return  $q->fetchColumn();
	}	


	public function delete(Personnage $perso)
	{
		 $this->_db->exec('DELETE FROM personnages_v3 WHERE id = '.$perso->id());
	}


	public function exists($info)
	{
	    if (is_int($info)) // On veut voir si tel personnage ayant pour id $info existe.
	    {
		    return (bool) $this->_db->query('SELECT COUNT(*) FROM personnages_v3 WHERE id = '.$info)->fetchColumn();
	    }
    
    	// Sinon, c'est qu'on veut vérifier que le nom existe ou pas.
    	else
    	
    	{
		    $q = $this->_db->prepare('SELECT COUNT(*) FROM personnages_v3 WHERE nom = :nom');
		    $q->execute([':nom' => $info]);
		    
		    return (bool) $q->fetchColumn();
    	}
	}	

	public function get($info)
	{

		if (is_int($info))
		{

   			$q = $this->_db->query('SELECT id, nom,  degats, timeEndormi, type, atout FROM personnages_v3 WHERE id = '. $info);
    	}	
	  
    	else
    	{
   			$q = $this->_db->query('SELECT id, nom,  degats, timeEndormi, type, atout FROM personnages_v3 WHERE nom = "'.$info.'"');
    	}

    	$donnees = $q->fetch(PDO::FETCH_ASSOC);

	    switch ($donnees['type'])
	    {
		      case 'guerrier': return new Guerrier($donnees);
		      case 'magicien': return new Magicien($donnees);
		      default: return null;
	    }
	}


	public function getlist($nom)
	{

		$persos = [];

    	$q = $this->_db->prepare('SELECT id, nom, degats,  type, atout, timeEndormi FROM personnages_v3 WHERE nom <> :nom ORDER BY nom ');

    	$q->execute([':nom' => $nom]);

    	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    	{
	      	switch ($donnees['type'])
	        {
		        case 'guerrier': $persos[] = new Guerrier($donnees); break;
		        case 'magicien': $persos[] = new Magicien($donnees); break;
	        }
      	}

	    $q->closeCursor();
   		
   		return $persos;

	}

	public function update(Personnage $perso)
	{

		$q = $this->_db->prepare('UPDATE personnages_v3 SET degats = :degats, timeEndormi = :timeEndormi, atout = :atout , type = :type WHERE id = :id');

	    $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
	    $q->bindValue(':type', $perso->type());
	    $q->bindValue(':timeEndormi', $perso->timeEndormi(),PDO::PARAM_INT);
	    $q->bindValue(':atout', $perso->atout(),PDO::PARAM_INT);
	    $q->bindValue(':id', $perso->id(),PDO::PARAM_INT);


	    $q->execute();

	    $q->closeCursor();
	}

}