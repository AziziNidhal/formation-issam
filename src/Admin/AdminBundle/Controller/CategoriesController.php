<?php

namespace Admin\AdminBundle\Controller;

use Admin\AdminBundle\Entity\Categorie;
use Admin\AdminBundle\Entity\PhotoCategorie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoriesController extends Controller
{
    public function ajoutPageAction()
    {
        return $this->render('AdminBundle:Categories:ajout.html.twig');
    }

    public function listePageAction()
    {
        return $this->render('AdminBundle:Categories:listecategories.html.twig');
    }




    public function ajoutCategorieAction(Request $request){



        if($request->getMethod() == "POST"){





            $em = $this->getDoctrine()->getManager();

            $data = json_decode($request->request->get("data"));
            $nom = $data->{"nom"};
            $desc = $data->{"desc"};

            $verifExsit = $em->getRepository("AdminBundle:Categorie")->findOneBy(array("nom"=>$nom));


            $rep = array();
            if(!$verifExsit){


                $categorie = new Categorie();

                $categorie->setNom($nom)->setDescription($desc);


                $em->persist($categorie);

                $em->flush();



                $uploadedPhoto = $request->files->get("photo");

                if($uploadedPhoto instanceof  UploadedFile){
                    $mimetype = $uploadedPhoto->getMimeType();

                    $extension = substr($mimetype , strpos($mimetype,"/")+1 , strlen($mimetype));

                    if($extension == "jpeg" || $extension == "jpg" || $extension == "png" ){

                        $photo = new PhotoCategorie();

                        $photo->setFile($uploadedPhoto);

                        $photo->upload($categorie->getId().".".$extension);

                        $photo->setName($categorie->getId().".".$extension);
                        $photo->setPath($categorie->getId().".".$extension);



                    }else{

                        $photo = new PhotoCategorie();
                        $photo->setName("avatarCategorie.png");
                        $photo->setPath("avatarCategorie.png");



                    }

                }else{

                    $photo = new PhotoCategorie();
                    $photo->setName("avatarCategorie.png");
                    $photo->setPath("avatarCategorie.png");

                }

                $em->persist($photo);

                $em->flush();
                $categorie->setPhoto($photo);

                $em->persist($categorie);

                $em->flush();


                $rep["success"]=1;
                $rep["message"] = "Catégorie ajoutée avec succes";





            }else{
                $rep["success"]=0;
                $rep["message"] = "Catégorie existe déja";


            }



            return new JsonResponse($rep);




        }else{



            die("route non disponible");
        }




    }


    public function getCategoriesJsonAction(Request $request){

        $em = $this->getDoctrine()->getEntityManager();

        $dataJson = json_decode($request->getContent() , true);

        $creteria = $dataJson["txt"];

        $qb = $em->createQueryBuilder();


        $query = $qb->select("c")
            ->from("AdminBundle:Categorie","c");

        if(strlen($creteria) > 2){
            $query->where("c.nom like :criteria");
        }

        $query->orderBy("c.id","desc");

        if(strlen($creteria) > 2){
            $query->setParameter("criteria","%".$creteria."%");
        }

        $categories = $query->getQuery()->getResult();


        $baseImg = $request->getScheme() . "://" . $request->getHttpHost() . $request->getBasePath() . "/uploads/categories/";

        $reponse = array();
        foreach ($categories as $category){
            array_push(
                $reponse,
                array(
                    "id"=>$category->getId(),
                    "nom"=>$category->getNom(),
                    "desc"=>$category->getDescription(),
                    "img" => $baseImg . $category->getPhoto()->getPath()
                )
            );

        }

        return new JsonResponse($reponse);

    }




    public function suppCategorieAction($id){

        $em = $this->getDoctrine()->getEntityManager();


        $categorie = $em->getRepository("AdminBundle:Categorie")->findOneBy(array("id"=>$id));


        if($categorie){
            $em->remove($categorie);
            $em->flush();

            die("Supprimé avec succes");

        }

        die("N existe pas");



    }



    public function modifierCategorieAction(Request $request){

        if($request->getMethod() == "POST"){

            $data = json_decode($request->request->get("data"));
            $id = $data->{"id"};
            $nom = $data->{"nom"};
            $desc = $data->{"desc"};


            $em = $this->getDoctrine()->getEntityManager();



            $categorie = $em->getRepository("AdminBundle:Categorie")->findOneBy(array("id"=>$id));



            $rep = array();

            if($categorie){





                $categorie->setNom($nom);
                $categorie->setDescription($desc);


                $uploadedPhoto = $request->files->get("photo");

                if($uploadedPhoto instanceof  UploadedFile){
                    $mimetype = $uploadedPhoto->getMimeType();

                    $extension = substr($mimetype , strpos($mimetype,"/")+1 , strlen($mimetype));

                    if($extension == "jpeg" || $extension == "jpg" || $extension == "png" ){

                        $photo = new PhotoCategorie();

                        $photo->setFile($uploadedPhoto);

                        $photo->upload($categorie->getId().".".$extension );

                        $photo->setName($categorie->getId().".".$extension);
                        $photo->setPath($categorie->getId().".".$extension);


                        $em->persist($photo);

                        $em->flush();

                        $categorie->setPhoto($photo);

                    }

                }



                $em->persist($categorie);
                $em->flush();

                $rep["success"]=1;
                $rep["message"] = "Catégorie modifiée avec succes";



            }else{


                $rep["success"]=0;
                $rep["message"] = "Catégorie inéxistante";



            }



            return new JsonResponse($rep);







        }else{

            die("Route non disponible");
        }




    }








}
