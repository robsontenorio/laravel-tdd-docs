@extends('testingdocs::layout') 

@section('content') 
    @if ($warning)
        <p class="warning-help">For more details check <a href="https://github.com/robsontenorio/laravel-tdd-docs">https://github.com/robsontenorio/laravel-tdd-docs</a>.</p>
        <div class="notification is-warning">{!! $warning !!}</div>        
    @endif

    @foreach($docs as $feature)           
    <div class="columns">
        <div class="column">
            <div class="feature">
                <h1 class="title">{{ $feature->title }}</h1>
                <p class="class"><strong>{{ '#'.$feature->tag }}</strong> {{ $feature->class }}</p>
                <h2>{!! $feature->description !!} </h2>
            </div>
        </div>
        <div class="column">
            @foreach($feature->scenarios as $scenario)

                <?php $class = (isset($scenario->error)) ? 'is-danger' : 'is-success' ?>
            
                <div class="scenario {{ $class }}" onclick="toggle('{{$feature->id}}-{{$scenario->method}}')"><strong>{{ $scenario->title }}</strong></div>                
                    
                    <div id="{{$feature->id}}-{{$scenario->method}}" class="wrapper">
                        @foreach($scenario->steps as $step)                
                            
                            <?php $class = (isset($step->error)) ? 'is-danger' : 'is-success' ?>
                            
                            <div class="step {{ $class }}">
                                {!! $step->text !!}
                                
                                @if (isset($step->error))
                                    <br>
                                    <small>{{ $step->error->exception }} (L {{ $step->error->line }})</small>                        
                                @endif
                            </div>
                        @endforeach
                    </div>
            @endforeach
        </div>
    </div>
    @endforeach 
</div>
    <?php // dd($docs) ?>


<style>
    .feature{
        background-color: #f5f5f5;
        padding: 10px 20px;
    }

    .feature strong{
        color: #c0c0c0;
    }

    .feature .title{
        margin-bottom: 0;
        font-size: 14pt;
    }

    .feature .class{
        font-size: 9pt;
        color: #848484;
        margin-bottom: 10px;
        margin-top: 5px;
    }

    .wrapper{
        display:none;
        margin-left: 50px;
    }

    .step{        
        margin-top: 1px;
        padding: 10px; 
        font-size: 10pt;
    }

    .wrapper .step:last-child{
        margin-bottom: 20px;
    }

    .step strong{
        color: white;
    }

    .scenario{
        padding: 10px;
        background-color: #c0c0c0;
        cursor: pointer;
        font-size: 10pt;
        margin-bottom: 1px;
    }

    .is-success{
        background-color: #b5dab5;
    }

    .is-danger{
        background-color: #e49797;
    }

    .warning-help{
        margin-bottom: 10px;
        font-size: 10pt;
    }



</style>

@endsection

@section('script')
<script>
    function toggle(div)
    {
        $('#' + div).toggle();
    }
</script> 
@endsection
