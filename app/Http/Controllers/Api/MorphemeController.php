<?php

namespace App\Http\Controllers\Api;
ini_set('memory_limit', '-1');
set_time_limit(0);
ini_set('max_execution_time', '-1');

use App\Http\Controllers\Controller;
use App\Http\Requests\MorphemeRequest;
use App\Http\Requests\SpecificWordIdRequest;
use App\Http\Requests\SpecificMorphemeRequest;
use App\Models\Morpheme;
use App\Models\MorphemeDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenApi\Annotations as OA;
use Validator;


/**
 * @OA\Info(
 *    title="Your super  ApplicationAPI",
 *    version="1.0.0",
 * )
 */

class MorphemeController extends Controller
{
    private function getQuranicData()
    {
        $localUrl  = "http://127.0.0.1:9000/api/quranic-data";
        $liveUrl  = "https://rq-www-lr.researchquran.org/api/get-quran-data";
        
        $response = \Cache::rememberForever('quranic-data', function(){
            $localUrl  = "http://127.0.0.1:9000/api/quranic-data";
            $liveUrl  = "https://rq-www-lr.researchquran.org/api/get-quran-data";

            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $liveUrl,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response, true);
        });

        return [
            'ayats' => @$response['quranicAyats'] ?? [],
            'words' => @$response['words'] ?? [],
            'rootWords' => @$response['rootWords'] ?? []
        ];
    }

    /**
     * @OA\Get(
     *    path="/api/get-root-words",
     *    operationId="rootWords",
     *    tags={"Morphemes"},
     *    summary="Get list of All Root Words",
     *    description="Get list of All Root Words",
     *    @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="Root Words Data Retrieved Successfully"),
     *             @OA\Property(
     *                  property="list",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer", example="1"),
     *                      @OA\Property(property="englishWord", type="string", example="A%5Edam"),
     *                      @OA\Property(property="rootWord", type="string", example="آدَم"),
     *                      @OA\Property(property="seperateRootWord", type="string", example="آدَم"),
     *                      @OA\Property(property="isExist", type="boolean", example="false"),
     *                 ),
     *            )
     *          )
     *       ),
     *     @OA\Response(
     *       response=500, description="Error",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="boolean", example="false"),
     *          @OA\Property(property="message", type="string", example="Something Went Wrong."),
     *       )
     *     ) 
     *  )
     */
    public function getRootWords(Request $request){
        try {
            $morphemesQuranicWords = MorphemeDetails::groupBy('root_word_id')->get()->toArray();
            $existingRootWordIDs = array_column($morphemesQuranicWords,'root_word_id');
            $result = $this->getQuranicData();
            $rootWords = $result['rootWords'];
            $list = [];
            if (!empty($rootWords)) {
                foreach ($rootWords as $key => $value) {
                    $list[] = [
                        'id' => $value['id'],
                        'englishWord' => $value['englishRootWord'],
                        'rootWord' => $value['rootWord'],
                        'seperateRootWord' => $value['seprateRootWord'],
                        'isExist' => in_array($value['id'], $existingRootWordIDs)
                    ];     
                }
            }

            $response = [
                'success' => true,
                'message' => 'Root Word Data Retrived Successfully',
                'list' => $list
            ];
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *    path="/api/get-morphemes",
     *    operationId="morphemes",
     *    tags={"Morphemes"},
     *    summary="Get list of Morphemes",
     *    description="Get list of Morphemes",
     *    @OA\RequestBody(
     *      required=true,
     *      description="Pass English Root Word In Order to Get data against specific Root Word",
     *      @OA\JsonContent(
     *         required={"rootWord"},
     *         @OA\Property(property="rootWord", type="string", format="text", example="Abb"),
     *      ),
     *    ),
     *    @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="Morphemes Data Retrieved Successfully"),
     *             @OA\Property(property="totalRecords", type="integer", example="140"),
     *             @OA\Property(
     *                  property="list",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="morpheme_no",
     *                         type="integer",
     *                         example="0"
     *                      ),
     *                      @OA\Property(
     *                         property="weight",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="word",
     *                         type="string",
     *                         example="مَصْتُ"
     *                      ),
     *                      @OA\Property(
     *                         property="group",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="subgroup",
     *                         type="number",
     *                         example="1.1"
     *                      ),
     *                 ),
     *            )
     *          )
     *       ),
     *     @OA\Response(
     *       response=500, description="Error",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="boolean", example="false"),
     *          @OA\Property(property="message", type="string", example="Something Went Wrong."),
     *       )
     *     ),
     *     @OA\Response(
     *       response=422, description="Error",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="The root word field is required."),
     *          @OA\Property(property="errors", type="object", example=""),
     *       )
     *     ) 
     *  )
     */
    public function index(MorphemeRequest $request){
        try {
            $validated = $request->validated();
            $rootWord = $validated['rootWord'];
            $quranicData = $this->getQuranicData();
            $rootWordID = collect($quranicData['rootWords'])->where('englishRootWord', $rootWord)->value('id');

            $result = MorphemeDetails::where('root_word_id', $rootWordID)->get()->toArray();
            $sheetWords = $this->arrangGroupsData($result, $rootWordID, $quranicData['words']);
            $list = $sheetWords['list'];
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(),'code' => $e->getCode()], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'Morphemes Data Retrived Successfully',
            'totalRecords' => count($list),
            'list' => $list
        ], 200);
    }


    /**
     * @OA\POST(
     *    path="/api/words-by-groups",
     *    operationId="wordByGroupSubGroups",
     *    tags={"Morphemes"},
     *    summary="Get Morphemes Data with Respect to Groups and Subgroups",
     *    description="Get Morphemes Data with Respect to Groups and Subgroups",
     *    @OA\RequestBody(
     *      required=true,
     *      description="Pass English Root Word In Order to Get data against specific Root Word",
     *      @OA\JsonContent(
     *         required={"rootWord"},
     *         @OA\Property(property="rootWord", type="string", format="text", example="Abb"),
     *      ),
     *    ),
     *    @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="Words data Retrived Successfully"),
     *             @OA\Property(
     *                  property="list",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="weight", type="string", example="Weight 1"),
     *                      @OA\Property(property="morphemeForm", type="string", example="أَبَبَ"),
     *                      @OA\Property(property="isReferenceExist", type="boolean", example="false"),
     *                      @OA\Property(
     *                         property="groups",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="key", type="string", example="Group 1"),
     *                             @OA\Property(property="isReferenceExist", type="boolean", example="false"),
     *                             @OA\Property(
     *                               property="subGroups",
     *                               type="array",
     *                               @OA\Items(
     *                                 @OA\Property(property="key", type="string", example="SubGroup 1.1"),
     *                                 @OA\Property(property="referenceWordCount", type="integer", example="0"),
     *                                 @OA\Property(property="arabicHeading", type="string", example="فِعْل أَمْر"),
     *                                 @OA\Property(property="englishHeading", type="string", example="imparative verb"),
     *                                 @OA\Property(
     *                                    property="mainWords",
     *                                    type="array",
     *                                    @OA\Items(
     *                                       @OA\Property(property="key", type="string", example="1.1.1"),
     *                                       @OA\Property(property="arabicHeading", type="string", example="فِعْل أَمْر"),
     *                                       @OA\Property(property="englishHeading", type="string", example="imparative verb"),
     *                                       @OA\Property(
     *                                          property="words", 
     *                                          type="array", 
     *                                          @OA\Items(
     *                                             @OA\Property(property="word", type="string", example="مَصْتُ"),
     *                                             @OA\Property(property="group", type="integer", example="1"),
     *                                             @OA\Property(property="weight", type="integer", example="1"),
     *                                             @OA\Property(property="subgroup", type="number", example="1.1"),
     *                                             @OA\Property(property="template", type="string", example="افعَلْ"),
     *                                             @OA\Property(property="morpheme_no", type="integer", example="0"),
     *                                             @OA\Property(property="word_number", type="integer", example="1.1.1"),
     *                                             @OA\Property(property="reference", type="boolean", example="false"),
     *                                             @OA\Property(property="matchingWords", type="array", @OA\Items()),
     *                                          ),
     *                                       ),
     *                                    )
     *                                 ), 
     *                               ),
     *                             ),
     *                         )
     *                      ),
     *                 ),
     *            )
     *          )
     *       ),
     *     @OA\Response(
     *       response=500, description="Error",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="boolean", example="false"),
     *          @OA\Property(property="message", type="string", example="Something Went Wrong."),
     *       )
     *     ),
     *     @OA\Response(
     *       response=422, description="Error",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="The root word field is required."),
     *          @OA\Property(property="errors", type="object", example=""),
     *       )
     *     )
     *  )
     */
    public function getWordByGroups(MorphemeRequest $request){
        try {
            $validated = $request->validated();
            $rootWord = $validated['rootWord'];
            $quranicData = $this->getQuranicData();

            $rootWordID = collect($quranicData['rootWords'])->where('englishRootWord', $rootWord)->value('id');

            $result = MorphemeDetails::where('root_word_id', $rootWordID)->get()->toArray();

            $sheetWords = $this->arrangGroupsData($result, $rootWordID, $quranicData['words']);

            $response = $this->arrangeDataWeigthWise($sheetWords);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'Words data Retrived Successfully',
            'list' => $response
        ]);
    }

    /**
     * @OA\Post(
     *    path="/api/words-by-groups-only",
     *    operationId="wordsByGroupOnly",
     *    tags={"Morphemes"},
     *    summary="Get list of words arrange by groups only",
     *    description="Get list of words arrange by groups only",
     *    @OA\RequestBody(
     *      required=true,
     *      description="Pass English Root Word In Order to Get data against specific Root Word",
     *      @OA\JsonContent(
     *         required={"rootWord"},
     *         @OA\Property(property="rootWord", type="string", format="text", example="Abb"),
     *      ),
     *    ),
     *    @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="Words By Groups Only Data Retrived Successfully"),
     *             @OA\Property(
     *                  property="list",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="key", type="string", example="Group 1"),
     *                      @OA\Property(
     *                          property="words",
     *                          type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="word", type="string", example="مَصْتُ"),
     *                              @OA\Property(property="group", type="integer", example="1"),
     *                              @OA\Property(property="weight", type="integer", example="1"),
     *                              @OA\Property(property="subgroup", type="number", example="1.1"),
     *                              @OA\Property(property="template", type="string", example="افعَلْ"),
     *                              @OA\Property(property="morpheme_no", type="integer", example="0"),
     *                              @OA\Property(property="word_number", type="integer", example="1.1.1"),
     *                              @OA\Property(property="reference", type="boolean", example="false"),
     *                              @OA\Property(property="matchingWords", type="array", @OA\Items()),
     *                          ),
     *                      ),
     *                 ),
     *            )
     *          )
     *       ),
     *     @OA\Response(
     *       response=500, description="Error",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="boolean", example="false"),
     *          @OA\Property(property="message", type="string", example="Something Went Wrong."),
     *       )
     *     ),
     *     @OA\Response(
     *       response=422, description="Error",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="The root word field is required."),
     *          @OA\Property(property="errors", type="object", example=""),
     *       )
     *     ) 
     *  )
     */
    public function wordsByGroupsOnly(MorphemeRequest $request){
        try {
            $validated = $request->validated();
            $rootWord = $validated['rootWord'];
            $quranicData = $this->getQuranicData();
            $rootWordID = collect($quranicData['rootWords'])->where('englishRootWord', $rootWord)->value('id');

            $result = MorphemeDetails::where('root_word_id', $rootWordID)->get()->toArray();
            $arrangeData = $this->arrangGroupsData($result, $rootWordID, $quranicData['words']);
            $collection = collect($arrangeData['list']);
            $groups = $arrangeData['groups'];
            $list = [];
            
            if (!empty($groups)) {
                foreach ($groups as $key => $value) {
                    $words = $collection->where('group', $value)->all();
                    $response[] = [
                        'key' => 'Group '. $value,
                        'words' => array_values($words)
                    ];
                }
            }    
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Words By Groups Only Data Retrived Successfully',
            'list' => $response 
        ],200);
    }

    public function getWordsByWordIdOnly(SpecificWordIdRequest $request){
        try {
            $validated = $request->validated();
            $rootWord = $validated['rootWord'];
            $wordId = $validated['wordId'];
            $quranicData = $this->getQuranicData();
            $rootWordID = collect($quranicData['rootWords'])->where('englishRootWord', $rootWord)->value('id');

            $result = MorphemeDetails::where('root_word_id', $rootWordID)->get()->toArray();
            $arrangeData = $this->arrangGroupsData($result, $rootWordID, $quranicData['words']);
            $list = array_filter($arrangeData['list'], function($item) use ($wordId){
                return (in_array($wordId, $item['word_id']));
            });
            $response = array_values($list);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Words Against Specific Word Id and Root Word Id Retrived Successfully',
            'list' => $response 
        ],200);
    }

    // FIND BY WORD ID
    public function getWordByWordIdOnly(SpecificMorphemeRequest $request){
        try {
            $validated = $request->validated();
            $wordId = $validated['wordId'];

            $pronoun = [
                ['ur'=> '', 'en'=> ''],
                ['ur'=> '', 'en'=> ''],
                ['ur'=> 'أنا', 'en'=> 'I (m)'],
                ['ur'=> 'نَحْنُ', 'en'=> 'we (m)'],
                ['ur'=> 'أنْتَ', 'en'=> 'you (m)'],
                ['ur'=> 'أنْتِ', 'en'=> 'You (f)'],
                ['ur'=> 'أنْتُما', 'en'=> 'You (2m)'],
                ['ur'=> 'أنْتُما', 'en'=> 'You (2f)'],
                ['ur'=> 'أنْتُم', 'en'=> 'You (pm)'],
                ['ur'=> 'أنْتُنَّ', 'en'=> 'you(pf)'],
                ['ur'=> 'هُوَ', 'en'=> 'he'],
                ['ur'=> 'هِيَ', 'en'=> 'she'],
                ['ur'=> 'هُمَا', 'en'=> 'they(2m)'],
                ['ur'=> 'هُما', 'en'=> 'they(2f)'],
                ['ur'=> 'هُمْ', 'en'=> 'they(pm)'],
                ['ur'=> 'هُنَّ', 'en'=> 'they(pf)']
            ];

            $weights = [
                'فَعَلَ',
                'فَعَّلَ',
                'فَاعَلَ',
                'أَفْعَلَ',
                'تَفَعَّلَ',
                'تَفَاعَلَ',
                'اِنْفَعَلَ',
                'اِفْتَعَلَ',
                'اِفْعَلَّ',
                'اسْتَفْعَلَ'
            ];
            
            $result = Morpheme::whereJsonContains('matching_words', $wordId)->orWhere('word_id', $wordId)->get()->toArray();
            $result = reset( $result );
            $sheetRef = explode( ':', $result['sheet_reference'][0] );
            $weight = $sheetRef[0];
            $morphemeNo = $sheetRef[1];

            $result = MorphemeDetails::where([
                'root_word_id' => $result['root_word_id'],
                'weight' => $weight,
                'morpheme_no' => $morphemeNo
            ])->get()->toArray();

            $result = reset( $result );

            $pronoun[$morphemeNo]['pronoun'] = end( $result['sheet_words'] )['word'];
            
            $list = array_filter($result['sheet_words'], function($item) use ($wordId){
                return (in_array($wordId, $item['word_id']));
            });

            $response = array_values($list);

            $response[0]['weight_form'] = $weights[ $weight - 1 ];
            $response[0]['morpheme_form'] = $pronoun[ $morphemeNo ];

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Quranic Word Against Specific Word Id Retrived Successfully',
            'list' => $response 
        ],200);
    }

    private function arrangGroupsData($record, $rootWordID, $words = []){
        $quranicWords = Morpheme::where('root_word_id',$rootWordID)->where('sheet_reference','!=', '[""]')->get()->toArray();
        $list = $groups = $subGroups = $wordNumbers = $brokenHeadings =  [];
        $sheetWords = array_column($record, 'sheet_words');
        
        if(!empty($sheetWords)){
            foreach ($sheetWords as $key => $value) {
                foreach($value as $val){
                    $checkArr = ['-','_', '0',''];
                    if(!in_array($val['word'], $checkArr) && !in_array($val['template'], $checkArr)){
                        /* CHECKING QURANIC REFERENCES START */
                        $reference = $val['weight'].':'.$val['morpheme_no'].':'.$val['group'].':'.$val['subgroup'].':'.$val['word_number'];

                        $isReferenceExist = array_filter($quranicWords, function($item) use($reference){
                            return (in_array($reference, $item['sheet_reference']));
                        });

                        $val['reference'] = count($isReferenceExist) > 0 ? true : false;
                        $matchingWords = [];
                        if(!empty($isReferenceExist)){
                            foreach ($isReferenceExist as $v) {
                                $matchWordID = $v['word_id'];
                                $surah = (int)substr($matchWordID, 1, 3);
                                $ayat = (int)substr($matchWordID, 4, 3);
                                $reference = (int)substr($matchWordID, 7, 3);
                                $matchingWords[] = [
                                    'wordId' => $matchWordID,
                                    'reference' => $surah.':'.$ayat.':'.$reference
                                ];
                                if(!empty($v['matching_words'])){
                                    foreach ($v['matching_words'] as $word) {
                                        if(!empty($word)){
                                            $matchingSurah = (int)substr($word, 1, 3);
                                            $matchingAyat = (int)substr($word, 4, 3);
                                            $matchingReference = (int)substr($word, 7, 3);
                                            $matchingWords[] = [
                                                'wordId' => $word,
                                                'reference' => $matchingSurah.':'.$matchingAyat.':'.$matchingReference
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                        usort($matchingWords, fn($item1, $item2) => $item1['wordId'] <=> $item2['wordId']);
                        $val['matchingWords'] = $matchingWords;
                        /* CHECKING QURANIC REFERENCES END */
                        
                        $list[] = $val;
                        $groupNumber = (int)$val['group'];
                        if(!in_array($groupNumber, $groups)) $groups[]= $groupNumber;
                        if(!in_array($val['subgroup'], $subGroups)) $subGroups[]= $val['subgroup'];
                        if(!in_array($val['word_number'], $wordNumbers)) $wordNumbers[]= $val['word_number'];

                        if($val['isbroken']==true && isset($val['broken_plurals']['word'])){
                            $check = collect($brokenHeadings)->where('heading', $val['broken_plurals']['word'])->count();
                            if(empty($check)){
                                $brokenWordGroups = [];
                                $reference = @$val['broken_plurals']['references'] ?? [];
                                foreach ($reference as $v) {
                                    $brokenWordGroups[] = explode('.', $v)[0];
                                }
                                $brokenHeadings[] = [
                                    'heading' => $val['broken_plurals']['word'] ?? '',
                                    'template' => $val['broken_plurals']['template'],
                                    'reference' => $reference,
                                    'groups' => array_unique($brokenWordGroups)
                                ];
                            }
                        }
                    }
                }
            }
        }
        sort($groups);
        sort($subGroups);
        natsort($wordNumbers);

        return [
            'groups' => $groups,
            'subGroups' => $subGroups,
            'wordNumbers' => $wordNumbers,
            'brokenHeadings' => $brokenHeadings,
            'list' => $list
        ];
    }

    private function arrangeDataWeigthWise($record, $specificWordId="")
    {
        $response = [];

        $groups = $record['groups'];
        $subGroups = $record['subGroups'];
        $wordNumbers = $record['wordNumbers'];
        $brokenHeadings = $record['brokenHeadings'];
            
        $list = $record['list'];

        $brokenWords = collect($list)->where('isbroken', true)->values();
        $brokenWordCollection = collect($brokenWords);
        
        $weights = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];

        foreach ($weights as $weight) {
            $morphemeForm = "";
            $groupsData = [];
            $isWeightReferenceExist = false;

            // NOT BROKEN PLURAL WORDS
            if($weight != 11){
                foreach ($groups as $key => $value) {
                    $isGroupReferenceExist = false;

                    $subgroups = array_filter($subGroups, function($item) use($value){
                        $expNumber = explode('.', $item);
                        return ($expNumber[0]== $value);
                    });
                    $subgroupsData =[];
                    foreach ($subgroups as $ikey => $val) {
                        $referenceCount = 0;
                        $subGroupWords = array_filter($wordNumbers, function($row) use($val){
                            $expRow = explode('.', $row);
                            $groupSubGroup = $expRow[0].'.'.$expRow[1];
                            return ($groupSubGroup == $val);     
                        });

                        $wordsData = [];
                        $englishHeading = $arabicHeading = "";
                        if(!empty($subGroupWords)){
                            foreach ($subGroupWords as $subGroupWordNumber) {
                                $actualWords = array_filter($list, function($wordItem) use($subGroupWordNumber, $weight){
                                    return($wordItem['weight']==$weight && $wordItem['word_number']==$subGroupWordNumber && $wordItem['isbroken']==false);
                                });
                                $actualWords = array_values($actualWords);
                                $referenceCount += collect($actualWords)->where('reference', true)->count();
                                if(!empty($actualWords)){
                                    // GET MORPHEME FORM
                                    if ($subGroupWordNumber=="1.1.1" && empty($morphemeForm)) {
                                        $eightWord = @$actualWords[8] ?? [];
                                        $morphemeForm = @$eightWord['word'] ?? "";
                                    }
                                    if(empty($englishHeading)){
                                        $arabicHeading = @$singleWord['ar'] ?? '';
                                        $englishHeading = @$singleWord['en'] ?? '';
                                    }
                                    // ARRANGE HEADINGS
                                    $singleWord = $actualWords[0];
                                    $arabicSubHeading = @$singleWord['sub_ar'] ?? '';
                                    $englishSubHeading = @$singleWord['sub_en'] ?? '';
                                    $wordsData[] = [
                                        'key' => $subGroupWordNumber,
                                        'arabicHeading' => $arabicSubHeading,
                                        'englishHeading' => $englishSubHeading,
                                        'words' => $actualWords
                                    ];    
                                }
                                
                            }
                        }
                        if(!empty($wordsData)){
                            $subgroupsData[] = [
                                'key' => 'Subgroup '. $val,
                                'referenceWordCount' => $referenceCount,
                                'arabicHeading' => $arabicHeading,
                                'englishHeading' => $englishHeading,
                                'mainWords' =>$wordsData
                            ];

                            if($referenceCount > 0){
                                $isGroupReferenceExist = true;
                                $isWeightReferenceExist = true;
                            }                          
                        }
                        
                    }

                    if(!empty($subgroupsData)){
                        $groupsData[] = [
                            'key' => 'Group '. $value,
                            'isReferenceExist' => $isGroupReferenceExist,
                            'subGroups' =>$subgroupsData
                        ];
                    }
                }
            }else{
                $groupsData = [];
                foreach ($groups as $group) {
                    $subgroupsData = [];
                    $groupBrokenHeadings = array_filter($brokenHeadings, function($item) use ($group){
                        return (in_array($group,$item['groups']));
                    });

                    $subGroupReference = false;
                    foreach ($groupBrokenHeadings as $key => $value) {
                        $wordsData = [];
                        $referenceCount = 0;
                        foreach ($value['reference'] as $val) {
                            $actualWords = $brokenWordCollection->where('word_number', $val)->values();

                            $referenceCount += collect($actualWords)->where('reference', true)->count();
                            $firstActualWord = count($actualWords) > 0 ? $actualWords[0] : [];

                            $firstActualWord = [];
                            $englishHeading = @$firstActualWord['sub_en'] ?? '';
                            $arabicHeading = @$firstActualWord['sub_ar'] ?? '';

                            if($referenceCount > 0){
                                $subGroupReference = true;
                            }

                            if(!empty($actualWords)){
                                $wordsData[] = [
                                    'key' => $val,
                                    'englishHeading' => $englishHeading,
                                    'arabicHeading' => $arabicHeading,
                                    'words' => $actualWords
                                ];    
                            }
                        }
                        if(!empty($wordsData)){
                            $subgroupsData[] = [
                                'key' => $value['heading'],
                                'template' => $value['template'],
                                'referenceWordCount' => $referenceCount,
                                'mainWords' => $wordsData
                            ];
                        }
                    }
                    if(!empty($subgroupsData)){
                        $groupsData[] = [
                            'key' => 'Group '. $group,
                            'isReferenceExist' => $subGroupReference,
                            'subGroups' => $subgroupsData
                        ];

                        if($subGroupReference == true){
                            $isWeightReferenceExist = true;
                        } 
                    }
                }
            }
            
            $response[] = [
                'weight' => 'Weight '. $weight,
                'morphemeForm' => $morphemeForm,
                'isReferenceExist' => $isWeightReferenceExist,
                'groups' => $groupsData
            ];    
        }

        return $response;
    }
}
