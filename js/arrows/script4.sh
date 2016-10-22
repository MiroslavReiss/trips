#/bin/sh
#
#http://www.imagemagick.org/Usage/draw/#arrows
#
#vector_head="path 'M 0,0  l -15,-5  +5,+5  -5,+5  +15,-5 z'"
vector_head="path 'M 0,0  l 15,-5  -5,+5  +5,+5  -15,-5 z'"

#15,15 is centre circle

#for COL in ffffcc ffeda0 fed976 feb24c fd8d3c fc4e2a e31a1c b10026
COLCYCLE=0
for COL in 'rgb(255,255,204)' 'rgb(255,237,160)' 'rgb(254,217,118)' 'rgb(254,178,76)' 'rgb(253,141,60)' 'rgb(252,78,42)' 'rgb(227,26,28)' 'rgb(177,0,38)'
do
echo ${COL}
COLCYCLESTR=`printf "%02d" ${COLCYCLE}`
CYCLE=0
for DEG in {0..359..5} #72 steps
do
BEARING=$(( $DEG + 90 )) #adjust for pointing back and coord system
CYCLESTR=`printf "%03d" ${CYCLE}`
#                   #transparent bkgnd
convert -size 32x32 xc:none \
        -draw "stroke black fill none  circle 15,15 15,16
               push graphic-context
                 stroke black fill ${COL}
                 translate 15,15 rotate ${BEARING}
                 line 0,0  16,0
                 translate 0,0
                 $vector_head
               pop graphic-context
              " \
        arrow_${COLCYCLESTR}_${CYCLESTR}.png
CYCLE=$(( $CYCLE + 1 ))
done
COLCYCLE=$(( $COLCYCLE + 1 ))
done

#tar -cvf arrows.tar arrow_* script*sh
#scp arrows.tar pberck@192.168.0.2:/Applications/MAMP/htdocs/oderland/berck.se/trips/js/
