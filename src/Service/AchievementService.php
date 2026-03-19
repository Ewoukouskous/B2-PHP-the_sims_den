<?php
require_once __DIR__ . '/../Repository/AchievementRepository.php';
require_once __DIR__ . '/../Repository/UserAchievementRepository.php';
require_once __DIR__ . '/../Repository/GameRepository.php';
require_once __DIR__ . '/../Repository/UserFavoriteRepository.php';
// This class will contain classes to check if some Achievement has to be unlocked
class AchievementService {
    // ATTRIBUTES
    private AchievementRepository $achievementRepository;
    private UserAchievementRepository $userAchievementRepository;
    private GameRepository $gameRepository;
    private UserFavoriteRepository $userFavoriteRepository;

    // CONSTRUCTOR
    public function __construct()
    {
        $this->achievementRepository = new AchievementRepository();
        $this->userAchievementRepository = new UserAchievementRepository();
        $this->gameRepository = new GameRepository();
        $this->userFavoriteRepository = new UserFavoriteRepository();
    }

    // METHODS
    // UTILITY METHOD : Add a userAchievement to a user in the DB
    private function unlockAchievement(int $idUser, Achievement $achievement) : void {
            $newUserAchievement = new UserAchievement($idUser,$achievement->getId());
            $this->userAchievementRepository->insert($newUserAchievement);

    }

    // UTILITY METHOD : Check if the achievement exists in the array and is not unlocked, if not we unlock it
    private function checkAndUnlock(int $idUser, array $userAchievements, string $achievementName) : void {
        if (isset($userAchievements[$achievementName]) && !$userAchievements[$achievementName]->isUnlocked()) {
            $this->unlockAchievement($idUser, $userAchievements[$achievementName]);
        }
    }

    // Check if 'favorite' achievement is unlocked, if yes call unlockAchievement
    public function checkFavoriteAchievements(int $idUser, int $idGame, string $action) : void {
        // Get all the achievement of the user and make a key->value array of it to check more easily if unlocked or not
        $achievementList = $this->achievementRepository->findAllWithUserProgress($idUser);
        $userAchievements = [];
        // key = achievement name | value = achievement object
        foreach ($achievementList as $achievement) {
            $userAchievements[$achievement->getAchievementName()] = $achievement;
        }

        // Check the action ('add' or 'delete')
        if ($action === 'add') {
            $favoriteCount = $this->userFavoriteRepository->countByUserId($idUser);
            // Start unlocking achievement by quantity threshold
            switch ($favoriteCount) {
                case 1 :
                    $this->checkAndUnlock($idUser, $userAchievements, "Néophyte");
                    break;
                case 5 :
                    $this->checkAndUnlock($idUser, $userAchievements, "Petit Joueur");
                    break;
                case 10 :
                    $this->checkAndUnlock($idUser, $userAchievements, "Compulsif");
                    break;
                case 20 :
                    $this->checkAndUnlock($idUser, $userAchievements, "Fanatique");
                    break;
            }
            // Now check wich achievement to unlock by gameType
            $game = $this->gameRepository->findById($idGame);
            $gameType = $game->getGameType();

            switch ($gameType) {
                case GameType::PC :
                    $this->checkAndUnlock($idUser, $userAchievements, "Bac à sable");
                    break;
                case GameType::CONSOLE :
                    $this->checkAndUnlock($idUser, $userAchievements, "Mode Histoire");
                    break;
                case GameType::SMARTPHONE :
                    $this->checkAndUnlock($idUser, $userAchievements, "FreePlay");
                    break;
            }

        } elseif ($action === 'delete') {
            // Unlock the only 'favorite' Achievement that exist
            $this->checkAndUnlock($idUser, $userAchievements, "Indécis");
        }
    }
}
