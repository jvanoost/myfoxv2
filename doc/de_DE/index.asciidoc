== Myfox Home

=== Présentation

Plugin permettant de dialoguer avec L'API Myfox api.myfox.me, pour les boxes domotique/alarme Myfox, également valable pour Evology (leroy merlin).

'''
=== Prérequis, installation

Vous devez au prealable recuperer vos identifiants sur http://api.myfox.me (connectez vous avec le meme identifiant que l'application iphone/android).

'''
=== Configuration

Aprés avoir fait une MISE A JOUR du plugin, vous devez à nouveau sauvegarder l'equipement.

'''
=== Création et utilisation des équipements  

Ajoutez un équipement, puis indiquez-y les informations récupérées sur http://api.myfox.me (My applications) :

- Client iD
- Client secret
- Indiquez votre identifiant Myfox
- Indiquez votre mot de passe Myfox

ATTENTION : Ne pas faire d'erreur avec le client id, client secret, id, password. En cas d'erreur, votre compte Myfox sera bloqué pendant 1 heure depuis votre IP.

'''
=== Fonctionnement du plugin

Une fois cliqué sur le bouton "sauvegarder" le plugin recupere vos capteurs de temperature, lumiere, actionneur prise, lumiere, module, garage... C'est pourquoi il ne faut pas d'erreur dans les identifiants.

Le plugin récupere toutes les minutes :

- L'etat de l'alarme (armement total, armement partiel, desarmé)
- La temperature et luminosité du capteur Myfox TA4007 (si vous en avez un)
	* Luminosite : paliers de retour de 1 à 6 . 1= pleine lumiere,  6 = obscurite 


- Dernier evenement de type "alarm" ( intrusion, defaut centrale, defaut pile ) sous la forme : "Alarme « Intrusion » déclenchée par l'appareil « ENTREE » (Sensibilité: 5). le xxxx à xxx."
	* S'il n'y a pas d'evenement dans la journée, la commande retourne : Aucun *

Le plugin permet : 

- L'activation partiele ou totale, de desarmer l'alarme
- L'activation ou desactivation d'un equipement (module, prise, lumiere, garage, portail)

