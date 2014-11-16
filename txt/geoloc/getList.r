#!/usr/bin/Rscript

countries <- read.csv("iso_3166_2_countries.csv",header=TRUE,dec=",",sep=",")
cities1 <- read.csv("unece/2014-1_UNLOCODE_CodeListPart1_utf8.csv",header=FALSE,
						  dec=",",sep=",")
cities2 <- read.csv("unece/2014-1_UNLOCODE_CodeListPart2_utf8.csv",header=FALSE,
						  dec=",",sep=",")
cities3 <- read.csv("unece/2014-1_UNLOCODE_CodeListPart3_utf8.csv",header=FALSE,
						  dec=",",sep=",")

countrieslist <- tolower(countries[,"Common.Name"])
cities <- rbind(cities1,cities2,cities3)
citieslist <- tolower(cities[,"V5"][cities[,"V5"]!=""])

write(countrieslist, file="countries.txt", sep="")
write(citieslist, file="cities.txt", sep="")
