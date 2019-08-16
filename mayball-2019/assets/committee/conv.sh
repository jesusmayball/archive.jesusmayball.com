for i in *.mp4;
  do name=`echo $i | cut -d'.' -f1`;
  echo $name;
  ffmpeg -i "$i" -b:v 0 -crf 25 -an "mpeg-4/${name}.mp4";
done
