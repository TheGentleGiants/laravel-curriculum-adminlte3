@extends('layouts.pdf')

<style>

    body{
        color: rgb(51, 51, 51)
    }
    .status {
        display: inline-block;
        width: 20%;
        vertical-align: top;
        margin-right: 4%;
    }
    .col{
        width: 20%;
        display: inline-block;
        vertical-align: top;
        min-height: 100px;
    }
    .row{
        width: 100%;
    }

    .item {
        width: 100%;
        /*min-height: 100px;*/
    }

    .card {
        border: 1px solid rgba(0, 0, 0, 0.125);
        border-radius: 5px;
        margin-bottom: 0.5rem;

    }
    h1{
        border-bottom: 3px solid {{ $kanban->color }};
    }
    h2{
        font-size: .85rem;
    }

    .card-header {
        font-size: .75rem;
        font-weight: 400;
        line-height: 1.5;
        text-align: left;
        word-wrap: break-word;
        box-sizing: border-box;
        margin-bottom: 0;
        padding: 0.5rem 1rem !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        position: relative;
        border-radius: calc(0.25rem - 1px) calc(0.25rem - 1px) 0 0;
        background-color: rgb(28, 160, 133);
        color: rgb(51, 51, 51);
    }
    .card-body{
        line-height: 1.5;
        text-align: left;
        font-size: .5rem;
        font-weight: 400;
        padding: 0.5rem 1rem !important;
        color: #6c757d !important;
        min-height: 30px;
    }
    .card-footer{
        font-size: 0.5rem;
        font-weight: 400;
        line-height: 1.5;
        text-align: left;
        font-family: "Open Sans", Arial, sans-serif !important;
        color: #333;
        word-wrap: break-word;
        box-sizing: border-box;
        background-color: rgba(0, 0, 0, 0.03);
        border-top: 0 !important;
        padding: 0.5rem 1rem !important;
        border-radius: 0 0 calc(0.25rem - 1px) calc(0.25rem - 1px);
    }
    .break-it{
        page-break-after: always;
    }
</style>

<h1>{{ $kanban->title }}</h1>
<?php
    $stati = $kanban->statuses->chunk(4);
?>
@foreach($stati as $chunk)
    <div class="break-it"></div>
    <?php
        $max = 0;
        foreach ($chunk as $status){
            $max = max($max, $status->items->count());
        }
        ?>
    @foreach($chunk as $n => $status)
        <div class="status">
            <h2>{{ $status->title }}</h2>
        </div>
    @endforeach
    @for($i = 0; $i < $max; $i++)
        <div class="row">
        @foreach($chunk as $status)
            @if($status->items->count() >= $i)
                <?php
                    $k = $status->items->get($i);
                    if(empty($k)):
                        ?>
                    <div class="col">
                        &nbsp;
                    </div>
                <?php
                    continue;
                    endif;
                ?>
                <div class="col">
                    <div class="item card border">
                        <div class="card-header" style="background-color: {{ $k->color ?? 'white' }}">
                            <div class="pb-1" style="line-height: 1">
                                <b>{{$k->title}}</b><br/>
                                <span style="font-size: .5rem;margin-top: 0.4rem">
                                    <i>{{$k->owner->username}}</i> - {{$k->created_at->format('d.m.Y H:i')}}
                                </span>
                            </div>
                        </div>

                            <div class="card-body">
                                @if($k->description != null)
                                {{ Str::limit( $k->description, 100, "...") }}
                                @endif
                            </div>

                    <!--div class="card-footer px-3 py-2 border-top-0">
                            {{$k->owner->username}}
                        </div-->
                    </div>
                </div>
            @else
                <div class="col">
                    &nbsp;
                </div>

            @endif
        @endforeach
        </div>
    @endfor
    </div>
@endforeach
