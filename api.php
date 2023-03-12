
<?php
// Replace with your OpenAI API key
$openai_api_key = 'xxx';

$post = file_get_contents("php://input");
$post = json_decode($post,true);
$prompt = $post['text'];

if(!$prompt){
    return err('missing parameter',500);
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_PROXY => "127.0.0.1",
    CURLOPT_PROXYPORT => 7890,
    CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode(array(
        'model' => 'gpt-3.5-turbo',
        "messages"=>[["role"=> "user", "content"=> $prompt]]
    )),
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $openai_api_key",
        "Content-Type: application/json"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

$complete = json_decode($response);
if( isset( $complete->choices[0]->message->content ) ) {
    $text = trim(str_replace( "\\n", "\n", $complete->choices[0]->message->content ),"\n");
} elseif( isset( $complete->error->message ) ) {
    $text = "服务器返回错误信息：".$complete->error->message;
} else {
    $text = "服务器超时或返回异常消息。";
}

if ($err) {
    return err('error',500,$err);
} else {
    // return ok('success',$response);
    echo json_encode( [
     "data" => $text,
     "msg" => 'success',
     "code" => "200",
 ] );
}


function ok ($msg='success',$data= null){
	header("content:application/json;chartset=uft-8");
	return json_encode(["code"=>200,"msg"=>$msg,'data'=>json_decode($data,true) ?? [] ]);
}

function err ($msg='error',$code=500,$data=false){
	header("content:application/json;chartset=uft-8");
	return json_encode (["code"=>$code,"msg"=>$msg,"data"=>json_decode($data,true)??[]]);
}
