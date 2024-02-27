<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .community-box {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
        }

        .community-box h1 {
            font-size: 30px;
            text-align: center;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .community-box form {
            text-align: center;
        }

        .community-box textarea {
            height: 150px;
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        .community-box .btn-primary {
            background-color: #1bb1d1;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        

        .community-box .post-box {
            padding-bottom: 20px;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .community-box .reply-box {
            padding-left: 3%;
            padding-bottom: 20px;
        }

        .reply-text, .timestamp {
            margin-bottom: 5px; 
        }

        .reply-box p {
            margin-bottom: 5px; 
        }

        .comment-text, .timestamp, .reply-text {
            margin-bottom: 5px; 
        }

        
        .post-box p, .reply-box p {
            margin-bottom: 5px; 
        }
    </style>
    <title>Styled Community Page</title>
</head>
<body>

<div class="community-box">

    <h1 style="color: green;">Eco-Connect</h1>

    <form action="{{url('add_comment')}}" method="POST">
        @csrf
        <textarea placeholder="Say something here..." name="comment" required=""></textarea>
        <br>
        <input type="submit" class="btn btn-primary" value="Post">
    </form>

    <div class="all-posts">
        <h1>Community Forums</h1>

        @foreach($comment as $comment)

        <div class="post-box">
            <b>From: {{$comment->name}}</b>
            <p class="comment-text" style="font-style: italic;">{{$comment->comment}}</p>
            <p class="timestamp" style="font-size: 14px">{{$comment->created_at->diffForHumans()}}</p>
            
            <a style="color: blue;" href="javascript::void(0);" onclick="reply(this)" data-Commentid="{{$comment->id}}">Reply</a>
            
            @if(Auth::check() && Auth::id() == $comment->user_id)
                <form method="post" action="{{ route('delete_comment', ['id' => $comment->id]) }}" style="display:inline;">
                    @csrf
                    @method('delete')
                    <button type="submit" style="color: red;">Delete</button>
                </form>
            @endif

            @foreach($reply as $rep)
                @if($rep->comment_id==$comment->id)
                    <div class="reply-box">
                        <b>Replied from: {{$rep->name}}</b>
                        <p class="reply-text" style="font-style: italic;">{{$rep->reply}}</p>
   
                        <p class="timestamp" style="font-size: 14px">{{$rep->created_at->diffForHumans()}}</p>
                        <a style="color: blue;" href="javascript::void(0);" onclick="reply(this)" data-Commentid="{{$comment->id}}">Reply</a>
                        @if(Auth::check() && Auth::id() == $rep->user_id)
                            <form method="post" action="{{ route('delete_reply', ['id' => $rep->id]) }}" style="display:inline;">
                                @csrf
                                @method('delete')
                                <button type="submit" style="color: red;">Delete</button>
                            </form>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

        @endforeach

        <!-- Reply Textbox -->

        <div style="display: none; padding-bottom: 20px;" class="replyDiv">
            <form action="{{url('add_reply')}}" method="POST">
                @csrf
                <input type="text" id="commentId" name="commentId" hidden="">
                <textarea style="height: 100px; width: 100%;" name="reply" placeholder="Write something here" required=""></textarea>
                <br>
                <button type="submit" class="btn btn-warning">Reply</button>
                <a href="javascript::void(0);" class="btn" onClick="reply_close(this)">Close</a>
            </form>
        </div>

    </div>

</div>

</body>
</html>
