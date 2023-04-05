<?php

namespace App\Controller;

use App\Entity\Relation;
use App\Form\RelationType;
use App\Repository\RelationRepository;
use MongoDB\Driver\ReadConcern;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class RelationController extends BaseController
{

    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        //return new Response('¡Bienvenido a mi sitio web!');
        return $this->render('base.html.twig');
    }

    #[Route('/ws/relation/new', name: 'relation_new')]
    public function new(Request $request, RelationRepository $relRepo): Response
    {
        $relation = new Relation();
        $form = $this->createForm(RelationType::class, $relation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($request->files as $file) {

                foreach ($file as $img) {

                    if (preg_match("/^image\/*/", $img->getMimeType())) {
                        dump($img);
                    }
                }
            }

            dd("PEPEPE");

            //dd($request->toArray());
            //$imageFile = $form->get('image')->getData();

            if ($form->has('image')) {
                $imageFile = $form->get('image')->getData();
            }

            $data = file_get_contents($form->get('data')->getData());
            $json = json_decode($data, true);

            //$relation->fromArray($json);

            if ($imageFile) {
                $newFilename = date('d-m-Y-H-i') . '_' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (\FileException $e) {
                    // handle exception if something happens during file upload
                    $this->jsonError(['error' => 'error-moviendo-la-imagen-al-servidor']);
                }

                // updates the 'image' property to store the file name
                // instead of its contents

                $ruta = $this->getParameter('uploads_directory') . '/' . $newFilename;

                //$base64Image = base64_encode(file_get_contents($ruta));
                $relation->setImage($ruta);
            }

            dd("PEPEPEPE");
            try {
                $relRepo->beginTransaction();
                $relRepo->save($relation, true);
                $relRepo->commit();

                return $this->jsonSuccess(['data' => $relation->toArray()]);
            } catch (\Exception $e) {
                $relRepo->rollback();
                return $this->jsonError(['error: ' => 'can-not-create-relation']);
            }

            //return $this->redirectToRoute('relation_index');
        }

        return $this->render('relation.html.twig', [
            'relation' => $relation,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/ws/relation/new2', name: 'relation_new2')]
    public function new2(Request $request, RelationRepository $relRepo): Response
    {
        $relation = new Relation();
        $form = $this->createForm(RelationType::class, $relation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $rdo = [];

            $data = file_get_contents($form->get('data')->getData());
            $json = json_decode($data, true);

            $relation->fromArray($json);

            if (array_key_exists('solutions', $json)) {

                $rdo['solutions'] = [];

                //dd($json['solutions']);

                foreach ($json['solutions'] as $solution) {

                    $sol = ['id' => $solution['id'], 'category' => $solution['category'],
                        'options' => $solution['options']];

                    if ($solution['image']) {

                        $newFilename = date('d-m-Y-H-i') . '_' . uniqid() . '.' . $solution['image']->guessExtension();

                        $solution['image']->move(
                            $this->getParameter('uploads_directory').'/category',
                            $newFilename
                        );

                        $ruta = $this->getParameter('uploads_directory') . '/category/' . $newFilename;
                        $sol['image'] = Relation::formatTo64($ruta);
                    }

                    $rdo['solutions'][] = $sol;
                }

            }
            $relation->setJson($rdo['solutions']);

            if (array_key_exists($json['image'], $json)) {

                $newFilename = date('d-m-Y-H-i') . '_' . uniqid() . '.' . $json['image']->guessExtension();

                $json['image']->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                $ruta = $this->getParameter('uploads_directory') . '/' . $newFilename;
                $relation->setImage(Relation::formatTo64($ruta));
            }

            try {
                $relRepo->beginTransaction();
                $relRepo->save($relation, true);
                $relRepo->commit();

                return $this->jsonSuccess(['data' => $relation->toArray()]);
            } catch (\Exception $e) {
                $relRepo->rollback();
                return $this->jsonError(['error: ' => 'can-not-create-relation']);
            }

            //return $this->redirectToRoute('relation_index');
        }

        return $this->render('relation.html.twig', [
            'relation' => $relation,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/ws/get-all-relations', name: 'get_all_relations')]
    public function getAllRelations(RelationRepository $relRepo): JsonResponse
    {
        $relations = $relRepo->findAll();

        $rdo = [];
        foreach ($relations as $relation) {
            $rdo[] = $relation->toArray();
        }

        return $this->jsonSuccess(['relations' => $rdo]);
    }

    #[Route('ws/get-relation/{uuid}', name: 'get_relation')]
    public function getRelation(string $uuid, RelationRepository $relRepo): JsonResponse
    {
        $relation = $relRepo->findOneBy(['uuid' => $uuid]);

        return $this->jsonSuccess(['relation' => $relation->toArray()]);
    }

    #[Route('ws/create-relation', name: 'crete_relation')]
    public function createRelation(Request $request, RelationRepository $relRepo): JsonResponse
    {
        //$relation = new Relation($request->getContent());

        $relation = new Relation();

        $relation->setTitle("Exercici 2");
        $relation->setSolutions(['solutions' => [['id' => '1', 'category' => 'Pérez', 'options' => ['Jaun', 'María', 'Pedro']],
            ['id' => '2', 'category' => 'García', 'options' => ['Ana', 'Luis', 'Marta']]]]);
        $relation->setOtherSolutions(["Pepe", "Paco"]);
        $relation->setMode("completo");


        try {
            $relRepo->beginTransaction();
            $relRepo->save($relation, true);
            $relRepo->commit();

            return $this->jsonSuccess(['data' => $relation->toArray()]);
        } catch (\Exception $e) {
            $relRepo->rollback();
            return $this->jsonError(['error: ' => 'can-not-create-relation']);
        }
    }

    #[Route('ws/edit-relation/{uuid}', name: 'edit_relation')]
    public function editRelation(string $uuid, Request $request, RelationRepository $relRepo): JsonResponse
    {
        $relation = $relRepo->findOneBy(['uuid' => $uuid]);

        $relation->fromJson($request->getContent());

        $relRepo->save($relation, true);

        try {
            var_dump("1");
            $relRepo->beginTransaction();
            var_dump("2");
            $relRepo->save($relation, true);
            var_dump("3");
            $relRepo->commit();

            var_dump("JAJAJAJJAJA");

            return $this->jsonSuccess(['data' => $relation->toArray()]);
        } catch (\Exception $e) {
            $relRepo->rollback();
            return $this->jsonError(['error: ' => 'can-not-edit-relation']);
        }
    }

    #[Route('ws/remove-relation/{uuid}', name: 'remove_relation')]
    public function removeRelation(string $uuid, RelationRepository $relRepo): JsonResponse
    {
        $relation = $relRepo->findOneBy(['uuid' => $uuid]);

        try {
            $relRepo->beginTransaction();
            $relRepo->remove($relation, true);
            $relRepo->commit();

            return $this->jsonSuccess(['data' => 'relation-removed-correctly']);
        } catch (\Exception $e) {
            $relRepo->rollback();
            return $this->jsonError(['error: ' => 'can-not-remove-relation']);
        }
    }

    #[Route('ws/verify-relation,response/{uuid}', name: 'verify_relation_response')]
    public function verifyRelationResponse(string $uuid, RelationRepository $relRepo, Request $request): JsonResponse
    {
        $solutions = [];

        $relation = $relRepo->findOneBy(['uuid' => $uuid]);

        $request->get('image')->getData();



        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->jsonError(['message' => 'Invalid json data']);
        }

        if (!array_key_exists('solutions', $data)) {
            return $this->jsonError(['message' => 'parent-key-not-exist (solutions)']);
        }

        foreach ($data['solutions'] as $clave => $valores) {

            if (!array_key_exists($clave, $relation->getSolutions())) {
                return $this->jsonError(['message' => 'key-not-exist-in-entity: ' . $clave]);
            }

            foreach ($valores as $valor) {

                if (!is_string($valor) || empty($valor)) {
                    return $this->jsonError(['message' => 'value-empty-or-not-string-at: (' . $clave . ',' . $valor . ').']);
                }

                if (in_array($valor, $relation->getSolutions()[$clave]['valores'])) {
                    $solutions[$clave][] = [$valor => true];
                } else {
                    $solutions[$clave][] = [$valor => false];
                }
            }
        }

        return $this->jsonSuccess(['solutions' => $solutions]);
    }



}
