<?php

namespace Mercator\Core\Contracts;

/**
 * Interface pour les modèles qui peuvent être identifiés par un UID unique
 * Format: PREFIX_ID (ex: "LSERVER_42")
 */
interface HasUniqueIdentifier
{
    /**
     * Retourne le préfixe d'identification du modèle
     * Ex: "LSERVER_", "WORK_", "PERIF_"
     */
    public function getPrefix(): string;

    /**
     * Retourne l'identifiant unique complet
     * Ex: "LSERVER_42"
     */
    public function getUID(): string;
}