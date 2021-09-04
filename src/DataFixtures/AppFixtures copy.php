<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Jeuxvideo;
use App\Entity\Comment;
use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');
        
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setPseudo('kirua')
                    ->setFirstName('elhadi')
                    ->setLastName('beddarem')
                    ->setEmail('elhadibeddarem@gmail.com')
                    ->setPassword($this->encoder->encodePassword($adminUser, 'password'))
                    ->setAgreeTerms(1)
                    ->setIsVerified(1)
                    ->setAvatar('https://pbs.twimg.com/profile_images/1184794615951560704/MuK0y8MA.png')
                    //->setImageFile('8c9b82bc035d2ec941c0eb426c31f34f79931076.gif')
                    ->addGrade($adminRole)
                    
                    
        ;


        $manager->persist($adminUser);

        // les utilisateurs

        $users = [];
        $genres = ['male', 'female'];

        for ($i=0; $i < 40; $i++) { 
            $user = new User();
            $genre = $faker->randomElement($genres);

            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99). '.jpg';

            $picture .= ($genre == 'male' ? 'men/' : 'women/'). $pictureId;

            $hash = $this->encoder->encodePassword($user, 'password');
            
            $user->setPseudo($faker->name())
                ->setFirstName($faker->firstname($genre))
                ->setLastName($faker->lastname)
                ->setEmail($faker->email)
                ->setPassword($hash)
                ->setIsVerified(1)
                ->setAgreeTerms(1)
                ->setAvatar('https://pbs.twimg.com/profile_images/1184794615951560704/MuK0y8MA.png')
                //->setImageFile('8c9b82bc035d2ec941c0eb426c31f34f79931076.gif')
            ;

            $manager->persist($user);
            $users[] = $user;
        }   

        for ($cat=0; $cat < 5; $cat++) { 
            $categorie = new Categorie();

            for ($jeu=0; $jeu < 20; $jeu++) { 
                $jeuxvideo = new Jeuxvideo();

                if (mt_rand(1, 12)) {
                    $comment = new Comment();

                    $comment->setTitle($faker->company())
                            ->setComment($faker->text())
                            ->setGame($jeuxvideo)
                            ->setUser($user)
                    ;
                    $manager->persist($comment);
                }

                $jeuxvideo->setName($faker->name())
                            ->setSlug($faker->slug())
                            ->setCoverImage($picture)
                            ->setDescription($faker->text())
                            ->setPrice($faker->numberBetween(0, 80))
                            ->addCategory($categorie)
                            ->addComment($comment)
                            ->setUser($user)
                ;

                $manager->persist($jeuxvideo);
            }

            $categorie->setName($faker->name())
                        ->setImage($picture)
                        //->setImageFile('2d541afdd35227a6116806f85d18eaf4a430ccc7.gif')
                        ->addGame($jeuxvideo)
            ;

                $manager->persist($categorie);

            

            
        }

            
        

        
        
        $manager->flush();
    }
}