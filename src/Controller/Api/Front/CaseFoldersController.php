<?php

namespace App\Controller\Api\Front;

use DateTime;
use App\Entity\CaseFolder;
use App\Service\AppMailer;
use App\Entity\Screenshots;
use App\Form\CaseFoldersType;
use App\Service\PictureService;
use App\Service\ReportedService;
use App\Repository\UserRepository;
use App\Repository\PlatformRepository;
use App\Repository\ReportedRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ScreenshotsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CaseFoldersController extends AbstractController
{
    private $entityManager;
    private $validator;
    private $pictureService;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, PictureService $pictureService)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->pictureService = $pictureService;
    }


    /**
     * Allow a user to create a report.
     *
     * This method allows a user to create a report by submitting a form. It handles the creation of a new `CaseFolder` entity, sets the user and reported entity, and uploads a screenshot.
     *
     * @param Request $request The HTTP request.
     * @param PlatformRepository $platformRepository The repository for platforms.
     * @param ReportedService $reportedService The reported service.
     *
     * @return JsonResponse A JSON response indicating the success of the report creation or validation errors.
     *
     * @Route("/api/front/report/add/content", name="app_api_front_report_content", methods={"GET","POST"})
     */
    public function reportContent(Request $request, PlatformRepository $platformRepository, ReportedService $reportedService, AppMailer $appMailer): JsonResponse
    {
        if($request->isMethod('POST')) {
            
            $user = $this->getUser();
            $existingCase = $this->entityManager->getRepository(CaseFolder::class)->findOneBy(['user' => $user]);
            
            if (!$this->isGranted('ROLE_CERTIFIED_USER') && $existingCase) {
                return $this->json(
                    ['error' => 'You cannot create another report if you are not a certified user'],
                    Response::HTTP_FORBIDDEN
                );
            }
            $case = new CaseFolder();
            $case->setUser($this->getUser());

            $jsonContent = json_decode($request->getContent());

            if((isset($jsonContent->reported) && $jsonContent->reported !== "") && (isset($jsonContent->platform) && $jsonContent->platform !== "")){
                $reported = $reportedService->findOrCreateReported($jsonContent->reported, $jsonContent->platform);
                $case->setReported($reported)
                ->setPlatform($platformRepository->find($jsonContent->platform))
                ->setContent($jsonContent->content);
            }       
            $errors = $this->validator->validate($case);

            if(count($errors) > 0) {
                $errorMessages = [];
                foreach($errors as $error) {
                    $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
                }
                return $this->json(
                    ['errors' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            if(count($errors) < 1){
                $this->entityManager->persist($case);
                $this->entityManager->flush();

                $appMailer->reportDone($user);
            }

            return $this->json(
                ["case" => $case],
                Response::HTTP_CREATED,
                [],
                ['groups' => ['cases_get_collection', 'case_get_item']]
            );
        }

        return $this->json(
            ['platformsChoice' => $platformRepository->findAll()],
            Response::HTTP_OK,
            [],
            ["groups" => "platforms_get_collection"]
        );
    }


    /**
     * Add pictures to a CaseFolder and the database.
     *
     * This method allows a user to add pictures to an existing `CaseFolder`. It validates the image format and associates it with the specified case. After processing, it returns a JSON response indicating the success of the operation or any potential errors.
     *
     * @param Request    $request The HTTP request.
     * @param CaseFolder $case    The target CaseFolder entity to which pictures will be added.
     *
     * @return JsonResponse A JSON response indicating the success of the picture upload or any validation errors.
     *
     * @Route("/api/front/report/add/{reference}", name="app_api_front_report_upload", methods={"GET", "POST"})
     */
    public function reportPictures(Request $request, CaseFolder $case)
    {
        if($request->isMethod('POST')){
            $imgToUpload = $request->files;

            foreach($imgToUpload as $img){
                
                $imgExtension = $img->getClientOriginalExtension();

                if($imgExtension !== "jpeg" && $imgExtension !== "png" && $imgExtension !== "webp"){
                    return $this->json(
                        ["error" => "One of the files has an unexpected format"],
                        Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
                        [],
                        []
                    );
                }

                $imgFile = $this->pictureService->add($img, $case->getReference());
                $screenshot = new Screenshots();
                $screenshot->setCaseFolder($case);
                $screenshot->setName($imgFile);
                $this->entityManager->persist($screenshot);
            }
            
            $this->entityManager->flush();
            
            return $this->json(
                ["message" =>"The file(s) have been uploaded."],
                Response::HTTP_CREATED,
                [],
                []
            );
        }
        return $this->json($case, 200, [], ["groups" => ["cases_get_collections", "case_get_screenshots"]]);
    }

    
    /**
     * Get the case folders possessed by the currently logged-in user.
     *
     * This method retrieves the case folders associated with the current user and returns them as a JSON response.
     *
     * @param UserRepository $userRepository The user repository to fetch user data.
     *
     * @Route("/api/front/cases/", name="app_api_front_cases", methods="GET")
     */
    public function cases(UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($this->getUser());

        return $this->json([
            "cases" => $user->getCaseFolders(),
        ],
            Response::HTTP_OK,
            [],
            ["groups" => "cases_get_collection"]
          );
    }


    /**
     * Request the data of a case folder by its ID.
     *
     * This method retrieves the data of a case folder specified by its ID and returns it as JSON data.
     *
     * @param CaseFolder $case The case folder entity.
     *
     * @return JsonResponse A JSON response containing the case folder data.
     *
     * @Route("/api/front/cases/{id<\d+>}", name="app_api_front_get_case", methods="GET")
     */
    public function readCase(CaseFolder $case = null): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $case, 'This case does not belong to you');

        return $this->json( 
        ["case" => $case ] ,
        Response::HTTP_OK ,
        [],
        ['groups' => ['cases_get_collection', 'case_get_item']]
        );
    }


    /**
     * Edit a case folder by its ID.
     *
     * This method updates the data of a case folder specified by its ID and returns the updated case folder as JSON data.
     *
     * @param CaseFolder $case The case folder entity.
     * @param Request $request The HTTP request.
     *
     * @return JsonResponse A JSON response containing the updated case folder data or validation errors.
     *
     * @Route("/api/front/cases/{id<\d+>}/update", name="app_api_front_patch_casefolder", methods="PATCH")
     */
    public function updateCase(CaseFolder $case = null, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $case, 'You can not modify a case that does not belong to you');
        if($case->getStatus() !== ["AWAITING"]){
            return $this->json(
                ['error' => 'Unable to modify a case currently being processed or processed.'],
                Response::HTTP_FORBIDDEN,
                [],
                []);
        }

        $json = json_decode($request->getContent(), true);

        $form = $this->createForm(CaseFoldersType::class, $case);
        $form->submit(array_merge($json), false);

        $errors = $this->validator->validate($case);
        
        if(count($errors) > 0){
            $errorMessages = [];   
                     
            foreach($errors as $error){
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json(
                ['errors' => $errorMessages],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        if(count($errors) < 1 && $form->isSubmitted()){
            $case->setUpdatedAt(new DateTime());
            $this->entityManager->persist($case);
            $this->entityManager->flush();
        }

        return $this->json(
            ["case" => $case],
            Response::HTTP_CREATED,
            [],
            ["groups" => ["cases_get_collection", "case_get_item"]]
        );
    }
}
