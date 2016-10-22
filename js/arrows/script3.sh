#/bin/sh
#
#http://www.imagemagick.org/Usage/draw/#arrows
#
#vector_head="path 'M 0,0  l -15,-5  +5,+5  -5,+5  +15,-5 z'"
vector_head="path 'M 0,0  l 15,-5  -5,+5  +5,+5  -15,-5 z'"

#15,15 is centre circle

for COL in "gray" "red" "green" "MediumForestGreen" "blue" "skyblue" "yellow" "gold2" "orange2" "purple3" "purple4" "SlateBlue3" "SlateBlue4"
do
echo ${COL}
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
        arrow_${COL}_${CYCLESTR}.png
CYCLE=$(( $CYCLE + 1 ))
done
done

#tar -cvf arrows.tar arrow_* script*sh
#scp arrows.tar pberck@192.168.0.2:/Applications/MAMP/htdocs/oderland/berck.se/trips/js/
