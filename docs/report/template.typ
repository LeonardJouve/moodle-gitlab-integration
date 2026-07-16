#let subtitle(body) = {
  set text(size:14pt,weight: "light")
  strong(body)
}

#let dep_fil_ori(dep,fil,ori) = {
  block()[*Department : * #dep]
  block()[*Program : * #fil]
  block()[*Specialization : * #ori]
}

#let student_date(student,date) = {
  block()[#student]
  block()[#date.display("[day] [month repr:long] [year]")]
}

#let proposed_supervised_by(name,institute) = {
  block()[Proposed and supervised by:]
  block()[#name]
  block()[#institute]
}


#let template(
  department : [department name],
  program : [program name],
  specialization : [specialization name],
  student : [student name],
  date : datetime.today(),
  supervisor : [supervisor name],
  institute : [supervisor institute name],
  abstract : [abstract],
  doc,
) = {
          
  set page(
    paper: "a4",
    margin: (top: 12em),
    header: context {
      image("images/Heig.svg")
    },
  )

  set text(
    font: "Calibri",
    size: 11pt,
    lang: "en"
  )
  
  show title: set text(size: 24pt)
  show title: set align(center)

  set par(
    justify: true,
    leading: 0.65em,
  )

  show heading.where(level: 1): it => {
    set align(center)
    set text(size: 24pt, weight: "bold")
    block(it.body)
    v(2em)
  }

  align(center)[
    #v(1.5cm)
    #title()
    #v(0.3cm)
    #subtitle[Bachelor Thesis]
    #v(1cm)
    #dep_fil_ori(department,program,specialization)
    #v(1cm)
    #student_date(student,date)
    #v(1cm)
    #proposed_supervised_by(supervisor,institute)
  ]
  
  pagebreak()
  set page(
    margin: auto,
    header: none,
    numbering: "I"
  )

  context counter(page).update(0)
  pagebreak()

  v(4cm)
  heading(level: 1, numbering: none, outlined: true)[Preamble]
  block()[
    This Bachelor’s thesis (hereinafter referred to as BT) is carried out at the end of the study program, with the aim of obtaining the Bachelor of Science HES-SO degree in Engineering.

    As an academic work, its content, without prejudice to its value, does not engage the responsibility of either the author, the Bachelor’s thesis jury, or the School.
    
    Any use, even partial, of this BT must comply with copyright law.
  ]
  v(4em)
  grid(
    columns: (60%, 40%),
    [], // Empty cell for the left side
    block()[
      HEIG-VD \
      The Head of the Department
    ]
  )
  v(10em)
  [Yverdon-les-bains, #date.display("[day] [month repr:long] [year]") ]


  pagebreak()

  
  pagebreak()
  v(4cm)
  heading(level: 1, numbering: none, outlined: true)[Authentication]
  block()[I, the undersigned, #student, hereby declare that I have completed this work independently and have not used any sources other than those expressly cited.]
  v(13em)
  grid(
    columns: (60%, 40%),
    [], // Empty cell for the left side
    block()[
      #student
    ]
  )
  v(10em)
  [Yverdon-les-bains, #date.display("[day] [month repr:long] [year]") ]
  
  pagebreak()

  pagebreak()

  v(4cm)
  heading(level: 1, numbering: none, outlined: true)[Abstract]
  block()[#abstract]
  
  pagebreak()
  pagebreak()
  outline(title: "Table of Contents")
  pagebreak()
  outline(
    title: [List of figures],
    target: figure.where(kind: image),
  )
  pagebreak()
  
  set page(
    margin: (
      top: 2.5cm,
      bottom: 2.5cm,
      left: 3cm,
      right: 2.5cm,
    ),
    footer: context {
      set text(size: 9pt)
      line(length: 100%)
      v(0.5em)
      document.title
      h(1fr)
      counter(page).display("1")
      h(1fr)
      datetime.today().display("[day] [month repr:long] [year]")
    },
    numbering: "1",
  )

  context counter(page).update(1)

  set heading(numbering: "1.")
  show heading.where(level: 1): it => {
    set text(size: 24pt, weight: "bold")
    block[#it.body]
  }
  
  show heading.where(level: 2): it => {
    set text(size: 18pt, weight: "bold")
    block[#counter(heading).display(it.numbering) #it.body]
    v(0.2em)
  }

  show heading.where(level: 3): it => {
    set text(size: 11pt, weight: "bold")
    block[#counter(heading).display(it.numbering) #emph[#it.body]]
    v(0.5em)
  }

  set list(indent: 1em)

  show raw.where(block: true): set align(center)
  show raw.where(block: true): set block(fill: white, inset: 1em, radius: 0.5em, width: auto,stroke: gradient.linear(red, blue))

  doc
}