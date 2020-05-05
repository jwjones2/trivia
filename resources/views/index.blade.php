@extends('layout.app')

@section('content')

<div class="title m-b-md text-white font-extrabold shadow-md font-basic absolute left-20 top-20">
    Praise Youth Trivia
</div>

<div class="" style="position: absolute; right: 5px; top: 10px; width: 400px; text-align: center;">
    <div class="text-white font-bold text-3xl font-comic">
        Life Savers
    </div>
    <img src="/images/lifesaver.png" style="width: 300px; height: 300px;" alt="">

    <img src="/images/lifesaver.png" style="width: 300px; height: 300px;" alt="">
</div>

<div class="flex-center position-ref full-height">

    <div class="flex flex-col w-5/12">

        <div class="flex-row">
            <img src="/images/Hardest.png" class="float-left pt-5" style="width: 300px; height: 300px;" alt="">
            
            <div id="rnd7" class="w-full bg-white font-extrabold text-accent text-3xl rounded-md h-40 mt-2 shadow-md align-middle hover:bg-accent hover:text-white">
                <div class="pt-12">
                    Round 7
                </div>

                <div id="ind7" class="bg-emoji text-white" style="cursor: pointer;" onclick="answer('7')">Answer Me!</div>
            </div>
        </div>

        <div class="flex-row">
            <img src="/images/Hard.png" class="float-left pt-5" style="width: 300px; height: 300px;" alt="">

            <div id="rnd6" class="w-full bg-white font-extrabold text-accent text-3xl rounded-md h-40 mt-2 shadow-md align-middle hover:bg-accent hover:text-white">
                <div class="pt-12">
                    Round 6
                </div>

                <div id="ind6" class="bg-emoji text-white" style="cursor: pointer;" onclick="answer('6')">Answer Me!</div>
            </div>

            <div id="rnd5" class="w-full bg-white font-extrabold text-accent text-3xl rounded-md h-40 mt-2 shadow-md align-middle hover:bg-accent hover:text-white">
                <div class="pt-12">
                    Round 5
                </div>

                <div id="ind5" class="bg-emoji text-white" style="cursor: pointer;" onclick="answer('5')">Answer Me!</div>
            </div>
        </div>

        <div class="flex-row">
            <img src="/images/Tougher.png" class="float-left pt-5" style="width: 300px; height: 300px;" alt="">

            <div id="rnd4" class="w-full bg-white font-extrabold text-accent text-3xl rounded-md h-40 mt-2 shadow-md align-middle hover:bg-accent hover:text-white">
                <div class="pt-12">
                    Round 4
                </div>

                <div id="ind4" class="bg-emoji text-white" style="cursor: pointer;" onclick="answer('4')">Answer Me!</div>
            </div>

            <div id="rnd3" class="w-full bg-white font-extrabold text-accent text-3xl rounded-md h-40 mt-2 shadow-md align-middle hover:bg-accent hover:text-white">
                <div class="pt-12">
                    Round 3
                </div>

                <div id="ind3" class="bg-emoji text-white" style="cursor: pointer;" onclick="answer('3')">Answer Me!</div>
            </div>
        </div>
        
        <div class="flex-row">
            <img src="/images/Easy.png" class="float-left pt-5" style="width: 300px; height: 300px;" alt="">

            <div id="rnd2" class="w-full bg-white font-extrabold text-accent text-3xl rounded-md h-40 mt-2 shadow-md align-middle hover:bg-accent hover:text-white">
                <div class="pt-12">
                    Round 2
                </div>

                <div id="ind2" class="bg-emoji text-white" style="cursor: pointer;" onclick="answer('2')">Answer Me!</div>
            </div>

            <div id="rnd1" class="w-full bg-white font-extrabold text-accent text-3xl rounded-md h-40 mt-2 shadow-md align-middle hover:bg-accent hover:text-white">
                <div class="pt-12">
                    Round 1
                </div>

                <div id="ind1" class="bg-emoji text-white" style="cursor: pointer;" onclick="answer('1')">Answer Me!</div>
            </div>
        </div>

    </div>
</div>
@stop

<script>
    function answer ( rnd ) {
        vex.dialog.open({
            unsafeMessage: "Stuff at round " + rnd
        });

        answered(rnd, "yes");
    }

    function answered ( rnd, mode ) {
        var indId = '#ind' + rnd;
        var rndId = '#rnd' + rnd;

        if ( mode == "yes" ) {
            $(indId).attr('class', 'bg-retrolime text-darkgray');
            $(indId).html("Answered.");

            var bg = ($(rndId).attr('class'));
            $(rndId).css({
                'background-color': '#888888',
                'color': '#ffffff'
            });
        } else {
            $(id).attr('class', 'bg-emoji text-white');
            $(indId).html("Answer Me!");

            var bg = ($(rndId).attr('class'));
            $(rndId).css({
                'background-color': '#ffffff',
                'color': '#BF8176'
            });
        }
    }
</script>