package main

import (
	"fmt"
	"time"
	"encoding/json"
	"log"
)

type Location struct {
	Lat float32
	Lng float32
	Timestamp time.Time
}

func main() {
§
	myLocation := Location{52.481765, 13.340901, time.Unix(1413667051, 0)}

    fmt.Printf("My Location: %+v\n", myLocation)
    fmt.Println(myLocation)
    fmt.Println(myLocation.Timestamp)

    data, err := json.Marshal(myLocation)
    if err != nil {
    	log.Fatal("Json Shit happens: ", err)
    }
    fmt.Printf("%s", data)


}
